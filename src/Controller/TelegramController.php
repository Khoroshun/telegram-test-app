<?php

namespace App\Controller;

use BotMan\BotMan\Cache\RedisCache;
use Symfony\Component\Dotenv\Dotenv;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Attachments\Location;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TelegramController extends AbstractController
{
    #[Route('/telegram', name: 'app_telegram')]
    public function index(): JsonResponse
    {

       // dd(__DIR__ . '/../../.env');
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../../.env');
        $dotenv->overload(__DIR__ . '/../../.env');

        $config = [
            "telegram" => [
                "token" => $_ENV['TELEGRAM_TOKEN']
            ]
        ];

        DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramDriver::class);
        DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramLocationDriver::class);
        DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramFileDriver::class);
        DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramPhotoDriver::class);
        DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramAudioDriver::class);
        DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramContactDriver::class);
        DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramVideoDriver::class);
        $botman = BotManFactory::create($config, null);

        $botman->hears('/start', function (BotMan $bot) {
            $bot->reply('Commands:');
            $bot->reply('Hello');
            $bot->reply('/what_is_my_id');
            $bot->reply('/weather');
            $bot->reply('/random_image');
        });

        $botman->hears('hello', function (BotMan $bot) {
            $name = $bot->getUser()->getFirstName() . ' ' . $bot->getUser()->getLastName();
            $name = trim($name);
            $bot->reply('Hello ' . $name);
        });

        $botman->hears('/what_is_my_id', function (BotMan $bot) {
            $id = $bot->getUser()->getId();
            $bot->reply($id);
        });

        /*
        $botman->hears('/weather', function ($bot) {
            $bot->startConversation(new WeatherConversation());
        });
        */

        $botman->hears('/random_image', function (BotMan $bot) {
            $width = mt_rand(800, 1920);
            $height = mt_rand(600, 1080);
            $attachment = new \BotMan\BotMan\Messages\Attachments\Image(
                'https://theoldreader.com/kittens/' . $width . '/' . $height . '/'
            );
            $message = \BotMan\BotMan\Messages\Outgoing\OutgoingMessage::create('random image')
                ->withAttachment($attachment);
            $bot->reply($message);
        });

        $botman->receivesImages(function ($bot, $images) {
            foreach ($images as $image) {
                $url = $image->getUrl();
                $bot->reply($url);
            }
        });

        // Start listening
        $botman->listen();


        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TelegramController.php',
        ]);
    }
}
