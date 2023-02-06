<?php

namespace App\Serializer\UserManagement;

use App\Entity\UserManagement\User;

/**
 * UserSerializer
 */
class UserSerializer
{
    public function serialize(User $user)
    {
        $workRoles = '';
        if (count($user->getWorkRoles()) > 0 ) {
            foreach ($user->getWorkRoles() as $workRole) {
                $workRoles .= $workRole.' ';
            }
        }
        return [
        		'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'fixe' => $user->getFixe(),
                'mobile' => $user->getMobile(),
                'fax' => $user->getFax(),
        		'adresse' => $user->getAdresse(),
                'cv' => $user->getCvUri(),
                'email' => $user->getEmail(),
                'gender' => $user->getGender()?$user->getGender()->getTitle():'',
                'function' => $user->getWorkFunction(),
                'roles' => $workRoles, 
                'attachment' => $user->getWorkAttachment()?$user->getWorkAttachment()->getTitle():'',                         
            ];     	
    }
}