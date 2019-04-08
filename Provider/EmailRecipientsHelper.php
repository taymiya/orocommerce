<?php

namespace Ibnab\Bundle\PmanagerBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\EmailBundle\Entity\Repository\EmailAwareRepository;
use Oro\Bundle\EmailBundle\Model\CategorizedRecipient;
use Oro\Bundle\EmailBundle\Model\EmailRecipientsProviderArgs;
use Oro\Bundle\EmailBundle\Model\Recipient;
use Oro\Bundle\EmailBundle\Model\RecipientEntity;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;
use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProvider;
use Oro\Bundle\EmailBundle\Tools\EmailAddressHelper;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Query\Result;

class EmailRecipientsHelper extends \Oro\Bundle\EmailBundle\Provider\EmailRecipientsHelper
{


    /**
     * @param object $object
     * @param ClassMetadata $objectMetadata
     *
     * @return RecipientEntity
     */
    public function createRecipientEntity($object, ClassMetadata $objectMetadata)
    {
        $identifiers = $objectMetadata->getIdentifierValues($object);
        if (count($identifiers) !== 1) {
            return null;
        }

        $organizationName = null;
        if ($this->getPropertyAccessor()->isReadable($object, static::ORGANIZATION_PROPERTY)) {
            $organization = $this->getPropertyAccessor()->getValue($object, static::ORGANIZATION_PROPERTY);
            if(is_string($organization)){
                $organizationName = $organization;
            }else{
                if(($organization)){
                $organizationName = $organization->getName();
                }
            }
        }

        return new RecipientEntity(
            $objectMetadata->name,
            reset($identifiers),
            $this->createRecipientEntityLabel($this->nameFormatter->format($object), $objectMetadata->name),
            $organizationName
        );
    }

  
}
