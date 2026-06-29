<?php

namespace App\Infrastructure\Anthropic\Client;

class ImageDescriptionGeneratorCustom implements ImageDescriptionGeneratorInterface
{
    public function describe(string $base64Image, string $mimeType): string
    {
        return $this->readable_random_sentence(40);
    }

    // @link https://gist.github.com/sepehr/3371339
    private function readable_random_sentence($length = 6) {
        $sentence = "";
        for ($i = 0; $i < $length; $i++) {
            $word_length = rand(3, 8);  // get random length for the word
            $word = $this->readable_random_word($word_length); // get the word
            $sentence .= $word . " "; // add the word to the sentence
        }

        return rtrim($sentence) . '.';
    }

    private function readable_random_word($length = 6) {
        $string = '';
        $vowels = array("a","e","i","o","u");
        $consonants = array(
            'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm',
            'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'
        );

        $max = $length / 2;
        for ($i = 1; $i <= $max; $i++)
        {
            $string .= $consonants[rand(0,19)];
            $string .= $vowels[rand(0,4)];
        }

        return $string;
    }
}
