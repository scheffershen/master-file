<?php

namespace App\Security\TemplateManagement;

use App\Entity\TemplateManagement\Section;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class SectionVoter extends Voter
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return \in_array($attribute, ['SECTION_EDIT'], true)
            && $subject instanceof Section;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case 'SECTION_EDIT':
                return $this->canEdit($subject, $token);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(Section $section, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        } elseif ($this->security->isGranted('ROLE_ADMIN') && $section->getEditable()) {
            return true;
        }

        return false;
    }
}
