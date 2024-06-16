<?php

namespace JaceApp\Jace\Services;

/* This is the worst thing I've ever written. How did I get here in my life? */
class MessageParsingService
{
    protected $emoteService;

    public function __construct(EmoteService $emoteService)
    {
       $this->emoteService = $emoteService; 
    }

    public function handle(string $message)
    {
        return [
            'message' => $message,
            /* 'blocks' => $this->parseMessage($message), Commenting this out, and going to try out markdown instead */
            'blocks' => [],
        ];
    }

   public function parseMessage($message) {
        $blocks = [];
        $paragraphs = preg_split("/\n\n+/", $message);

        foreach ($paragraphs as $paragraph) {
            if (preg_match("/^```(.*?)```$/s", $paragraph, $codeBlockMatch)) {
                $blocks = $this->createCodeBlock($codeBlockMatch[1]);
            } else {
                $blocks = $this->createTextBlock($paragraph);
            }
        }

        return $blocks;
    }

    private function createCodeBlock($text) {
        return [
            'type' => 'style',
            'elements' => [
                ['type' => 'text', 'text' => trim($text)
            ]],
        ];
    }

    private function createTextBlock($paragraph) {
        $elements = [];
        $emojiRegex = $this->emoteService->generateEmojiRegex();
        $parts = preg_split("/(\*.*?\*|_.*?_|`.*?`|<.*?>|(?:$emojiRegex))/", $paragraph, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($parts as $part) {
            $elements[] = $this->parsePart($part);
        }

        return [
            'type' => 'style',
            'elements' => $elements,
        ];
    }

    private function parsePart($part) {
        $regex = $this->emoteService->generateEmojiRegex();
        $emojiKeyMap = $this->emoteService->keyMap();

        if (preg_match('/^\*(.*?)\*$/', $part, $match)) {
            return ['type' => 'text', 'text' => $match[1], 'style' => ['bold' => true]];
        } elseif (preg_match('/^_(.*?)_$/', $part, $match)) {
            return ['type' => 'text', 'text' => $match[1], 'style' => ['italic' => true]];
        } elseif (preg_match('/\b(' . $regex . ')\b/', $part, $match)) {
            $emoji = $emojiKeyMap[$match[1]] ?? null;
            if ($emoji) {
                return ['type' => 'emoji', 'name' => $match[1]];
            }
        } elseif (preg_match('/^<([^>]+)\|([^>]+)>$/', $part, $match)) {
            return ['type' => 'link', 'url' => $match[1], 'text' => $match[2]];
        } elseif (preg_match('/^`(.*?)`$/', $part, $match)) {
            return ['type' => 'text', 'text' => $match[1], 'style' => ['code' => true]];
        } else {
            return ['type' => 'text', 'text' => $part];
        }
    }
}
