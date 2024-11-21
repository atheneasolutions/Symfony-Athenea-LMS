<?php

namespace Athenea\LMS\Service;

use Athenea\LMS\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;

class UserService {

    public function __construct(private DocumentManager $documentManager)
    {
        
    }
}