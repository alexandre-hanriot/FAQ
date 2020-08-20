<?php

namespace App\Command;

use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QuestionsDeactivateCommand extends Command
{
    protected static $defaultName = 'app:questions:deactivate';

    private $questionRepository;
    private $entityManager;

    /**
     * On récupère les services nécessaires via le constructeur de la commande
     */
    public function __construct(QuestionRepository $questionRepository, EntityManagerInterface $entityManager)
    {
        $this->questionRepository = $questionRepository;
        $this->entityManager = $entityManager;
        // On appelle le constructeur parent (nécessaire pour instancier la commande)
        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setDescription('Deactivates outdated questions')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');
        $question = new Question('enter number of days for activating: ');
        $days = $helper->ask($input, $output, $question);
        $output->writeln('You choose: '.$days. ' days');
        $io->title('+ + + Deactivate All +'.$days.' days question + + +');

        // Créer la requête de modifications des questions concernées
        $updatedQuestions = $this->questionRepository->findAllOutdated();

        if ($updatedQuestions > 0) {
            $io->success($updatedQuestions . ' questions updated.');
        } else {
            $io->warning('No questions updated.');
        }

        return 0;
    }
}
