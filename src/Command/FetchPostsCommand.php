<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\Post;

class FetchPostsCommand extends Command
{
    protected static $defaultName = 'app:fetch-posts';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Pobieranie postów z api i zapis ich do lokalnej bazy');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://jsonplaceholder.typicode.com/posts');
        $postsData = $response->toArray();

        foreach ($postsData as $postData) {
            $userResponse = $client->request('GET', 'https://jsonplaceholder.typicode.com/users/' . $postData['userId']);
            $userData = $userResponse->toArray();

            $post = new Post();
            $post->setTitle($postData['title']);
            $post->setBody($postData['body']);
            $post->setAutor($userData['name']);

            $this->entityManager->persist($post);
        }

        $this->entityManager->flush();

        $output->writeln('Posty zostały pobrane i zapisane z sukcesem.');

        return Command::SUCCESS;
    }
}
