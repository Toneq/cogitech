<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Post;
use App\Entity\User;

class FetchPostsCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:fetch-posts')
            ->setDescription('Uzyskanie postów poprzez API i zapis do db.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', 'https://jsonplaceholder.typicode.com/posts');
        $postsData = $response->toArray();

        foreach ($postsData as $postData) {
            $userResponse = $httpClient->request('GET', 'https://jsonplaceholder.typicode.com/users/'.$postData['userId']);
            $userData = $userResponse->toArray();

            $post = new Post();
            $post->setTitle($postData['title']);
            $post->setBody($postData['body']);
            $post->setAuthor($userData["name"]);

            $this->entityManager->persist($post);
        }

        $this->entityManager->flush();

        $output->writeln('Wszystkie posty zostały wgrane do bazy danych.');

        return 0;
    }
}
