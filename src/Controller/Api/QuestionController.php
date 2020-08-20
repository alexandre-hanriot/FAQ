<?php

namespace App\Controller\Api;

use App\Entity\Question;
use App\Repository\UserRepository;
use App\Repository\QuestionRepository;
use App\Repository\TagRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class QuestionController extends AbstractController
{
    /**
     * Liste des questions
     *
     * @Route("/api/questions", name="api_questions_get", methods={"GET"})
     */
    public function getQuestions(QuestionRepository $questionRepository)
    {
        // Les questions non modérées
        $questions = $questionRepository->findBy(['isBlocked' => false]);
       
        return $this->json($questions, 200, [], ['groups' => 'questions_get']);
    }

    /**
     * Ajouter une question
     *
     * @Route("/api/questions", name="api_questions_add", methods={"POST"})
     */
    public function addQuestion(Request $request, UserRepository $userRepository, TagRepository $tagRepository, DenormalizerInterface $denormalizer, ValidatorInterface $validator)
    {
        // Le JSON attendu est (mettre vos ids de BDD à vous !)

    // {
    //     "question": {
    //         "title": "Les tests, ça va être chaud ?",
    //         "body": "Oui, je pense",
    //         "user": 1,
    //         "tags": [
    //             100, // Avec un faux tag
    //             2
    //         ]
    //     }
    // }
        // 1. On récupère le contenu JSON
        $data = json_decode($request->getContent());
        // dump($data);

        // 2. On va dénormaliser la question ($data->question)
        // cf : le schéma : https://symfony.com/doc/current/components/serializer.html#deserializing-an-object
        $question = $denormalizer->denormalize($data->question, Question::class);

        // 2a. L'entité est-elle valide ?
        $errors = $validator->validate($question);
        // $errors est une ConstraintViolationList = se comporte comme un tableau
        if (count($errors) !== 0) {
            $jsonErrors = [];
            foreach ($errors as $error) {
                $jsonErrors[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }

            return $this->json($jsonErrors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        dd($question);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($question);
        $entityManager->flush();

        // REST nous dit 201 + Location:
        return $this->redirectToRoute('api_questions_show', ['id' => $question->getId()], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/questions/{id<\d+>}", name="api_questions_show", methods={"GET"})
     */
    public function show(Question $question = null)
    {
        if ($question === null) {
            return $this->json(['message' => 'Question non existante.'], Response::HTTP_NOT_FOUND);
        }

        if ($question->getIsBlocked()) {
            return $this->json(['message' => 'Question modérée.'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($question, 200, [], ['groups' => 'questions_get']);
    }

    /**
     * Supprimer une question 
     * 
     * @Route("/api/questions/{id<\d+>}", name="api_questions_delete", methods={"DELETE"})
     */
    public function deleteQuestion($id, Question $question = null)
    {
        if ($question === null) {
            return $this->json(['message' => 'Question non existante.'], Response::HTTP_NOT_FOUND);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($question);
        $entityManager->flush($question);

        return $this->json([
            'message' => 'Question supprimée',
            'id' => $id,
        ], Response::HTTP_OK);
    }

}
