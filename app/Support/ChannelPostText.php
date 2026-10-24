<?php

namespace App\Support;

use App\Models\Post;
use Illuminate\Support\Str;

/**
 * The texts the Telegram channel gets for one listing (HTML for Telegram's parse_mode):
 *  - fullText():  every filled-in detail of the listing, up to the 4096 characters of a message
 *  - caption():   the same text when it fits a photo caption (1024 visible characters), otherwise a
 *                 short one that points to the full text sent under the album
 */
class ChannelPostText
{
    public const CAPTION_LIMIT = 1024;

    public const MESSAGE_LIMIT = 4096;

    private const MONTHS = ['yanvar', 'fevral', 'mart', 'aprel', 'may', 'iyun', 'iyul', 'avgust', 'sentabr', 'oktabr', 'noyabr', 'dekabr'];

    private const EMOJI = [
        'It' => '🐕',
        'Mushuk' => '🐈',
        'Qushlar' => '🦜',
        'Kaptar' => '🦜',
        'Parrandalar' => '🦜',
        'Baliq' => '🐠',
        'Kemiruvchilar' => '🐹',
    ];

    private const YOUNG = [
        'It' => 'kuchuklari',
        'Mushuk' => 'mushukchalari',
    ];

    /** Breed names kept in English on the site, as the channel's readers know them. Keys are lowercase. */
    private const BREEDS = [
        'german shepherd' => 'Nemis ovcharkasi',
        'labrador' => 'Labrador',
        'labrador retriever' => 'Labrador',
        'golden retriever' => 'Golden retriver',
        'husky' => 'Xaski',
        'siberian husky' => 'Sibir xaskisi',
        'rottweiler' => 'Rotveyler',
        'pug' => 'Mops',
        'bulldog' => 'Buldog',
        'french bulldog' => 'Fransuz buldogi',
        'english bulldog' => 'Ingliz buldogi',
        'poodle' => 'Pudel',
        'dachshund' => 'Taksa',
        'doberman' => 'Doberman',
        'pomeranian' => 'Shpits',
        'beagle' => 'Bigl',
        'boxer' => 'Bokser',
        'great dane' => 'Nemis dogi',
        'caucasian shepherd' => 'Kavkaz ovcharkasi',
        'central asian shepherd' => 'Alabay',
        'british shorthair' => 'Britaniya kalta junli',
        'british longhair' => 'Britaniya uzun junli',
        'persian' => 'Fors mushugi',
        'siamese' => 'Siam mushugi',
        'maine coon' => 'Maine-kun',
        'sphynx' => 'Sfinks',
        'scottish fold' => 'Shotland vislouxiy',
        'bengal' => 'Bengal mushugi',
        'ragdoll' => 'Regdoll',
        'russian blue' => "Rus ko'k mushugi",
    ];

    private ?bool $split = null;

    public function __construct(private readonly Post $post)
    {
        $post->loadMissing('category', 'user');
    }

    /**
     * What goes under the first photo: the full text when it fits, a short one when it does not.
     */
    public function caption(): string
    {
        return $this->needsSplit() ? $this->shortCaption() : $this->render($this->description());
    }

    /**
     * True when the full text is too long for a photo caption and has to follow the album as a message.
     */
    public function needsSplit(): bool
    {
        return $this->split ??= self::visibleLength($this->render($this->description())) > self::CAPTION_LIMIT;
    }

    /**
     * Everything, cut down to one message: when the description is too long it ends with "… davomi saytda".
     */
    public function fullText(): string
    {
        $description = $this->description();
        $text = $this->render($description);
        $over = self::visibleLength($text) - self::MESSAGE_LIMIT;

        if ($over > 0) {
            $keep = max(0, mb_strlen($description) - $over - 30);
            $text = $this->render(rtrim(mb_substr($description, 0, $keep)) . '… davomi saytda');
        }

        return $text;
    }

    /**
     * Inline buttons for the full message under the album.
     */
    public function buttons(): array
    {
        return [[
            ['text' => "👉 E'lonni ko'rish", 'url' => $this->postUrl()],
            ['text' => '✉️ Sotuvchiga yozish', 'url' => $this->sellerUrl()],
        ]];
    }

    /**
     * What the first photo's price label says.
     */
    public function priceLabel(): string
    {
        $post = $this->post;

        if ($post->price === null) {
            return 'Kelishiladi';
        }

        $number = number_format((float) $post->price, 0, '.', ' ');

        return match ($post->currency) {
            'USD' => '$' . $number,
            'UZS', null => "$number so'm",
            default => "$number {$post->currency}",
        };
    }

    public static function visibleLength(string $html): int
    {
        return mb_strlen(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function postUrl(): string
    {
        return route('posts.show', $this->post);
    }

    private function sellerUrl(): string
    {
        return $this->post->user ? route('sellers.show', $this->post->user) : $this->postUrl();
    }

    private function description(): string
    {
        return trim((string) $this->post->description);
    }

    private function render(string $description): string
    {
        $post = $this->post;
        $user = $post->user;

        $price = '💰 <b>' . e($this->priceLabel()) . '</b>' . ($post->price !== null && $post->is_negotiable ? ' (kelishiladi)' : '');
        $place = collect([$post->region, $post->location])->filter()->implode(', ');

        $seller = [];
        if ($user) {
            $seller[] = '👤 <b>' . e($user->name) . '</b>';
            if ($phone = self::phone($user->phone)) {
                $seller[] = '📞 ' . e($phone);
            }
            if ($tg = ltrim((string) $user->telegram_username, '@')) {
                $seller[] = '✈️ @' . e($tg);
            }
        }

        $sections = [
            $this->headline(),
            $price . ($place !== '' ? "\n📍 " . e($place) : ''),
            $this->quote($this->mainRows()),
            $this->quote($this->extraRows()),
            $description !== '' ? '📝 ' . e($description) : '',
            implode("\n", $seller),
            '<a href="' . e($this->postUrl()) . '">👉 E\'lonni ko\'rish</a>  |  <a href="' . e($this->sellerUrl()) . '">✉️ Sotuvchiga yozish</a>',
            $this->hashtags() . " · № {$post->id}" . ($post->created_at ? ' · ' . $this->date() : ''),
        ];

        return implode("\n\n", array_filter($sections, fn ($s) => $s !== ''));
    }

    private function shortCaption(): string
    {
        $post = $this->post;
        $place = collect([$post->region, $post->location])->filter()->implode(', ');

        return implode("\n\n", array_filter([
            $this->headline(),
            '💰 <b>' . e($this->priceLabel()) . '</b>' . ($post->price !== null && $post->is_negotiable ? ' (kelishiladi)' : '')
                . ($place !== '' ? "\n📍 " . e($place) : ''),
            "Batafsil ma'lumot pastda 👇",
        ]));
    }

    /**
     * "🐕 Nemis ovcharkasi kuchuklari": the breed (or the category when there is none), plural when more than one.
     */
    private function headline(): string
    {
        $post = $this->post;
        $category = (string) $post->category?->name;
        $name = $this->breed() ?: $category ?: $post->title;
        $young = ($post->quantity ?? 1) > 1 ? ' ' . (self::YOUNG[$category] ?? 'bolalari') : '';

        return (self::EMOJI[$category] ?? '🐾') . ' <b>' . e($name . $young) . '</b>';
    }

    private function breed(): string
    {
        $breed = trim((string) $this->post->breed);

        return self::BREEDS[mb_strtolower($breed)] ?? $breed;
    }

    /**
     * Category, breed, age, sex, count, colour, then the number-type details (weight, size, height...).
     *
     * @return list<array{0: string, 1: string}>
     */
    private function mainRows(): array
    {
        $post = $this->post;
        $profile = $post->profile();

        $gender = null;
        if ($profile->usesGender()) {
            $gender = ($post->male_quantity || $post->female_quantity)
                ? "{$post->male_quantity} erkak, {$post->female_quantity} urg'ochi"
                : ['male' => 'Erkak', 'female' => "Urg'ochi"][$post->gender] ?? null;
        }

        $rows = [
            ['Kategoriya', $post->category?->name],
            [$profile->breedLabel(), $this->breed()],
            [$profile->forForm()['age_label'], $profile->usesAge() ? $post->age : null],
            ['Jinsi', $gender],
            ['Soni', $post->quantity ? "{$post->quantity} ta" : null],
            ['Rangi', $profile->usesColor() ? $post->color : null],
        ];

        $headline = $this->breed() ?: (string) $post->category?->name;
        if (filled($post->title) && mb_strtolower(trim($post->title)) !== mb_strtolower($headline)) {
            array_unshift($rows, ["E'lon nomi", $post->title]);
        }

        return array_merge($this->clean($rows), $this->detailRows(true));
    }

    /**
     * The other details of the category (vaccination, papers, health, ...).
     */
    private function extraRows(): array
    {
        return $this->detailRows(false);
    }

    /**
     * The category's own details under the names the site shows, split into number fields and the rest.
     */
    private function detailRows(bool $numbers): array
    {
        $profile = $this->post->profile();
        $details = (array) $this->post->details;
        $rows = [];

        foreach ($profile->fields() as $key => $field) {
            if ((($field['type'] ?? '') === 'number') !== $numbers) {
                continue;
            }

            $value = $details[$key] ?? null;
            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            if (is_bool($value)) {
                $value = $value ? 'Ha' : "Yo'q";
            } elseif (is_array($value)) {
                $value = implode(', ', $value);
            } elseif (($field['type'] ?? '') === 'number') {
                $value = rtrim(rtrim(number_format((float) $value, 2, '.', ' '), '0'), '.') . ' ' . ($field['unit'] ?? '');
            }

            $rows[] = [$field['label'], trim((string) $value)];
        }

        return $rows;
    }

    private function clean(array $rows): array
    {
        return array_values(array_filter($rows, fn ($r) => filled($r[1])));
    }

    private function quote(array $rows): string
    {
        $rows = $this->clean($rows);

        if (! $rows) {
            return '';
        }

        return '<blockquote>' . implode("\n", array_map(fn ($r) => '<b>' . e($r[0]) . ':</b> ' . e($r[1]), $rows)) . '</blockquote>';
    }

    /**
     * "#it #nemisovcharkasi #toshkent #sotiladi": category, breed, first word of the region, fixed tag.
     */
    private function hashtags(): string
    {
        $tag = fn (?string $v) => Str::of((string) $v)->lower()->replaceMatches('/[^\p{L}\p{N}]+/u', '')->toString();

        return collect([
            $tag($this->post->category?->name),
            $tag($this->breed()),
            $tag(Str::of((string) $this->post->region)->before(' ')->toString()),
            'sotiladi',
        ])->filter()->unique()->map(fn ($t) => "#$t")->implode(' ');
    }

    /**
     * "24-oktabr, 2026"
     */
    private function date(): string
    {
        $d = $this->post->created_at;

        return $d->day . '-' . self::MONTHS[$d->month - 1] . ', ' . $d->year;
    }

    /**
     * "+998 90 123 45 67" for Uzbek numbers (with or without the country code); anything else as typed.
     */
    public static function phone(?string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $raw);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 9) {
            $digits = '998' . $digits;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '998')) {
            return sprintf('+998 %s %s %s %s', substr($digits, 3, 2), substr($digits, 5, 3), substr($digits, 8, 2), substr($digits, 10, 2));
        }

        return trim((string) $raw);
    }
}
