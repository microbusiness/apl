<?php

namespace App\Translation\Loader;

use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class CustomApiLoader implements LoaderInterface
{
    public function load(mixed $resource, string $locale, string $domain = 'messages'): MessageCatalogue
    {
        $catalogue = new MessageCatalogue($locale);

        // --- ВАША ЛОГИКА ---
        // Например, запрос к БД:
        // $messages = $this->dbRepository->findMessages($locale, $domain);
        // $catalogue->add($messages, $domain);
        // ------------------

        // Пример данных
        $messages = ['hello' => 'Привет из БД'];
        $catalogue->add($messages, $domain);

        return $catalogue;
    }
}
