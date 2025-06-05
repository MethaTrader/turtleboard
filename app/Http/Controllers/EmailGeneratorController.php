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
        'outlook' => ['outlook.com', 'hotmail.com'], // Both Outlook domains
        'icloud' => 'icloud.com', // Fixed: changed 'iCloud' to 'icloud' to match validation
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
            'provider' => 'required|in:gmail,outlook,icloud,yahoo'
        ]);

        $userData = $this->generateRealSlavicName();
        $emailLocal = $this->generateEmailLocal($userData);

        // Handle multiple domains for Outlook
        $domain = $this->getDomain($validated['provider']);
        $email = $emailLocal . '@' . $domain;

        // Calculate the "best choice" score
        $score = $this->calculateEmailScore($emailLocal, $userData);

        return response()->json([
            'email' => $email,
            'meta' => [
                'first_name' => $userData['first'],
                'last_name' => $userData['last'],
                'score' => $score,
                'is_best_choice' => $score >= 75 // Threshold for "Best choice ✨"
            ]
        ]);
    }

    private function getDomain(string $provider): string
    {
        $domains = $this->domains[$provider];

        if (is_array($domains)) {
            // For Outlook, favor outlook.com over hotmail.com (80% vs 20%)
            if ($provider === 'outlook') {
                $random = mt_rand(1, 100);
                return $random <= 80 ? 'outlook.com' : 'hotmail.com';
            }

            // For other providers with multiple domains, use random selection
            return $domains[array_rand($domains)];
        }

        return $domains;
    }

    private function calculateEmailScore(string $emailLocal, array $userData): int
    {
        $score = 0;

        // 1. Length score (optimal length is 8-16 characters)
        $length = strlen($emailLocal);
        if ($length >= 8 && $length <= 16) {
            $score += 20;
        } elseif ($length >= 6 && $length <= 20) {
            $score += 10;
        }

        // 2. Complexity score - presence of separators and modifications
        $hasUnderscore = strpos($emailLocal, '_') !== false;
        $hasDot = strpos($emailLocal, '.') !== false;
        $hasNumbers = preg_match('/\d/', $emailLocal);
        $numberCount = preg_match_all('/\d/', $emailLocal);

        if ($hasUnderscore || $hasDot) {
            $score += 15;
        }

        if ($hasNumbers) {
            $score += 15;
            // Bonus for multiple numbers
            if ($numberCount >= 2) {
                $score += 10;
            }
        }

        // 3. Name modification score - avoid exact first.last patterns
        $firstName = strtolower($userData['first']);
        $lastName = strtolower($userData['last']);

        // Check for exact common patterns
        $exactPatterns = [
            $firstName . '.' . $lastName,
            $firstName . '_' . $lastName,
            $firstName . $lastName,
            $lastName . '.' . $firstName
        ];

        $isExactMatch = false;
        foreach ($exactPatterns as $pattern) {
            if ($emailLocal === $pattern || strpos($emailLocal, $pattern) === 0) {
                $isExactMatch = true;
                break;
            }
        }

        if (!$isExactMatch) {
            $score += 25; // High bonus for not being exact first.last
        } else {
            $score -= 10; // Penalty for exact matches
        }

        // 4. Uniqueness indicators - check for common simple patterns
        $commonPatterns = [
            '/^[a-z]+\.[a-z]+$/',     // john.doe
            '/^[a-z]+[a-z]+$/',       // johndoe
            '/^[a-z]+\d{1,2}$/',      // john1, john12
            '/^[a-z]{1}\.[a-z]+$/'    // j.doe
        ];

        $isCommonPattern = false;
        foreach ($commonPatterns as $pattern) {
            if (preg_match($pattern, $emailLocal)) {
                $isCommonPattern = true;
                break;
            }
        }

        if (!$isCommonPattern) {
            $score += 20;
        }

        // 5. Year pattern bonus (like 1970, 1980s, etc.)
        if (preg_match('/19[0-9]{2}|20[0-2][0-9]/', $emailLocal)) {
            $score += 15; // Years make emails more unique
        }

        // 6. Avoid gibberish penalty
        // Check for too many consecutive consonants or vowels
        if (preg_match('/[bcdfghjklmnpqrstvwxyz]{5,}/', $emailLocal)) {
            $score -= 25; // Heavy penalty for consonant clusters
        }
        if (preg_match('/[aeiou]{4,}/', $emailLocal)) {
            $score -= 15; // Penalty for vowel clusters
        }

        // 7. Name recognition bonus
        // Check if parts of the original name are still recognizable
        $namePartsFound = 0;
        if (strpos($emailLocal, substr($firstName, 0, 3)) !== false) $namePartsFound++;
        if (strpos($emailLocal, substr($lastName, 0, 3)) !== false) $namePartsFound++;

        if ($namePartsFound >= 1) {
            $score += 10; // Bonus for name recognition
            if (($hasNumbers || $hasUnderscore || $hasDot) && $namePartsFound >= 1) {
                $score += 10; // Extra bonus for good balance
            }
        }

        // 8. Special patterns bonus
        // Mixed case variations, numbers in middle, etc.
        if (preg_match('/[a-z]+\d+[a-z]+/', $emailLocal)) {
            $score += 10; // Numbers in middle are good
        }

        // 9. Length variety bonus
        if ($length >= 12 && $hasNumbers && ($hasUnderscore || $hasDot)) {
            $score += 10; // Longer emails with complexity are more unique
        }

        // 10. Avoid too simple penalties
        if ($length < 6) {
            $score -= 15; // Too short emails are likely taken
        }

        // Ensure score is between 0 and 100
        return max(0, min(100, $score));
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
            // Enhanced patterns for better uniqueness
            ['{first}.{last}{num}', 15],
            ['{first}_{last}{num}', 15],
            ['{f}{last}{num}', 12],
            ['{first}{last_mod}{num}', 12], // Modified last name
            ['{first_mod}.{last}{num}', 10], // Modified first name
            ['{first}{num}{l}', 8],
            ['{f}.{last}{num}', 8],
            ['{first_mod}_{last_mod}{num}', 7], // Both modified
            ['{last}{first_short}{num}', 6], // Surname first
            ['{first}.{num}.{last_mod}', 4], // Complex pattern
            ['{f}{first_short}{last_mod}{num}', 3], // Very unique
        ];

        $selectedPattern = $this->getWeightedRandomPattern($patterns);
        $number = $this->generateSlavicNumber();

        // Create modified versions of names for more uniqueness
        $firstMod = $this->modifyName(strtolower($data['first']));
        $lastMod = $this->modifyName(strtolower($data['last']));
        $firstShort = substr(strtolower($data['first']), 0, rand(3, min(5, strlen($data['first']))));

        $replacements = [
            '{first}' => strtolower($data['first']),
            '{last}' => strtolower($data['last']),
            '{first_mod}' => $firstMod,
            '{last_mod}' => $lastMod,
            '{first_short}' => $firstShort,
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

    private function modifyName(string $name): string
    {
        if (strlen($name) < 3) {
            return $name; // Too short to modify safely
        }

        $modifications = [
            // Double a letter (common in Slavic names)
            function($name) {
                $pos = rand(1, strlen($name) - 2);
                return substr($name, 0, $pos) . $name[$pos] . substr($name, $pos);
            },
            // Add a consonant
            function($name) {
                $consonants = ['n', 's', 'r', 't', 'l', 'v', 'k'];
                $letter = $consonants[array_rand($consonants)];
                return $name . $letter;
            },
            // Shorten the name intelligently
            function($name) {
                if (strlen($name) > 4) {
                    return substr($name, 0, rand(3, strlen($name) - 1));
                }
                return $name;
            },
            // Change ending
            function($name) {
                if (strlen($name) > 3) {
                    $endings = ['ov', 'ev', 'in', 'yn', 'uk', 'ek'];
                    return substr($name, 0, -1) . $endings[array_rand($endings)];
                }
                return $name;
            }
        ];

        // 40% chance to modify the name
        if (rand(1, 100) <= 40) {
            $modifier = $modifications[array_rand($modifications)];
            return $modifier($name);
        }

        return $name;
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
            fn() => rand(80, 99),                  // 80s-90s years
            fn() => rand(70, 99),                  // 70s-90s years
            fn() => rand(1, 999),                  // Random number
            fn() => '',                            // No number (reduced chance)
            fn() => rand(10, 99),                  // Two digits
            fn() => rand(100, 999),                // Three digits
            fn() => '19' . rand(70, 99),           // Birth years 1970-1999
            fn() => '20' . sprintf('%02d', rand(0, 5)), // 2000s years
            fn() => rand(1, 9) . rand(0, 9) . rand(0, 9), // Mixed digits
        ];

        return (string) $formats[array_rand($formats)]();
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