<?php

namespace App\Security\Voter;

use App\Entity\Question;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class QuestionVoter extends Voter
{
    private $security;

    /**
     * On récupère le service Security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * L'action demandée existe-t-elle dans ce Voter ?
     * ET l'entité transmise est-elle concernée par ce Voter ?
     */
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['edit', 'validate-answer', 'can-answer', 'show'])
            && $subject instanceof Question;
    }

    protected function voteOnAttribute($attribute, $question, TokenInterface $token)
    {
        // User connecté
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // les conditions de vérification qui autorisent le vote (true ou false)
        switch ($attribute) {
            case 'edit':

                // Le user est-il auteur de la question ?
                if ($this->isOwner($question, $user)) {
                    return true;
                }

                // Le user est-il MODERATOR ?
                if ($this->security->isGranted('ROLE_MODERATOR')) {
                    return true;
                }

                break;
            case 'show':

                // La question est-elle non bloquée ?
                if (! $question->getIsBlocked()) {
                    return true;
                }

                // Le user est-il MODERATOR ?
                if ($this->security->isGranted('ROLE_MODERATOR')) {
                    return true;
                }

                break;
            
            case 'validate-answer':
                return $this->isOwner($question, $user);
            
            case 'can-answer':
                return $question->getActive();
        }

        return false;
    }

    private function isOwner($question, $user)
    {
        // Est-l'auteur de la question ?
        return $question->getUser() === $user;
    }
}
