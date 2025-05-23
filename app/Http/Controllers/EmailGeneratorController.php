<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Faker\Factory;
use Faker\Provider\ru_RU\Person as RussianPerson;
use Faker\Provider\uk_UA\Person as UkrainianPerson;

class EmailGeneratorController extends Controller
{
    private array $domains = [
        'gmail' => 'gmail.com',
        'outlook' => 'outlook.com',
        'rambler' => 'rambler.ru',
        'yahoo' => 'yahoo.com'
    ];

    private array $countries = [
        'ru_RU' => 40, // Russian 40%
        'uk_UA' => 30, // Ukrainian 30%
        'sk_SK' => 20, // Slovak 20%
        'pl_PL' => 10  // Polish 10% (similar to Slovak names)
    ];

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:gmail,outlook,rambler,yahoo'
        ]);

        $userData = $this->generateRealSlavicName();
        $emailLocal = $this->generateEmailLocal($userData);

        return response()->json([
            'email' => $emailLocal . '@' . $this->domains[$validated['provider']],
            'meta' => [
                'first_name' => $userData['first'],
                'last_name' => $userData['last']
            ]
        ]);
    }

    private function generateRealSlavicName(): array
    {
        $country = $this->getWeightedRandomCountry();
        $faker = Factory::create($country);

        // Add region-specific providers
        switch ($country) {
            case 'ru_RU':
                $faker->addProvider(new RussianPerson($faker));
                break;
            case 'uk_UA':
                $faker->addProvider(new UkrainianPerson($faker));
                break;
        }

        return [
            'first' => $this->transliterate($faker->firstName()),
            'last' => $this->transliterate($faker->lastName()),
            'f' => mb_substr($this->transliterate($faker->firstName()), 0, 1),
            'l' => mb_substr($this->transliterate($faker->lastName()), 0, 1),
        ];
    }

    private function getWeightedRandomCountry(): string
    {
        $rand = mt_rand(1, 100);
        $cumulative = 0;

        foreach ($this->countries as $country => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $country;
            }
        }

        return 'ru_RU'; // fallback
    }

    private function transliterate(string $name): string
    {
        $cyr = [
            'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
            'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
            'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
            'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я',
            'č','š','ž','ť','ď','ľ','ň','ô','á','é','í','ó','ú','ý','ä','ň'
        ];

        $lat = [
            'a','b','v','g','d','e','e','zh','z','i','y','k','l','m','n','o','p',
            'r','s','t','u','f','h','ts','ch','sh','sht','','y','','e','yu','ya',
            'A','B','V','G','D','E','E','Zh','Z','I','Y','K','L','M','N','O','P',
            'R','S','T','U','F','H','Ts','Ch','Sh','Sht','','Y','','E','Yu','Ya',
            'c','s','z','t','d','l','n','o','a','e','i','o','u','y','a','n'
        ];

        $name = str_replace($cyr, $lat, $name);

        // Remove any remaining non-Latin characters
        return preg_replace('/[^a-zA-Z]/', '', $name);
    }

    private function generateEmailLocal(array $data): string
    {
        $patterns = [
            // Russian/Slavic style patterns
            ['{first}.{last}{num}', 30],
            ['{first}_{last}{num}', 20],
            ['{f}{last}{num}', 15],
            ['{first}{l}{num}', 10],
            ['{first}{num}', 10],
            ['{last}{num}', 10],
            ['{first}.{num}.{last}', 5],
        ];

        $selectedPattern = $this->getWeightedRandomPattern($patterns);
        $number = $this->generateSlavicNumber();

        $replacements = [
            '{first}' => strtolower($data['first']),
            '{last}' => strtolower($data['last']),
            '{f}' => strtolower($data['f']),
            '{l}' => strtolower($data['l']),
            '{num}' => $number,
        ];

        $email = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $selectedPattern
        );

        return $this->cleanEmail($email);
    }

    private function getWeightedRandomPattern(array $patterns): string
    {
        $total = array_sum(array_column($patterns, 1));
        $rand = mt_rand(1, $total);
        $cumulative = 0;

        foreach ($patterns as [$pattern, $weight]) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $pattern;
            }
        }

        return $patterns[0][0]; // fallback
    }

    private function generateSlavicNumber(): string
    {
        $formats = [
            fn() => rand(80, 99),                  // 80s-90s birth years
            fn() => rand(0, 23),                   // Current year
            fn() => rand(1, 999),                  // Random number
            fn() => '',                            // No number
            fn() => rand(1, 9) . rand(0, 9),       // Two digits
            fn() => rand(1, 9) . rand(0, 9) . rand(0, 9), // Three digits
        ];

        return $formats[array_rand($formats)]();
    }

    private function cleanEmail(string $email): string
    {
        // Remove consecutive dots/underscores
        $email = preg_replace(['/\.+/', '/_+/'], ['.', '_'], $email);

        // Trim special chars from ends
        $email = trim($email, '._-');

        // Ensure max length
        return substr($email, 0, 30);
    }
}