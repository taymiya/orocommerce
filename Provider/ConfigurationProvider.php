<?php

namespace Ibnab\Bundle\PmanagerBundle\Provider;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\LayoutBundle\Provider\CustomImageFilterProviderInterface;
use Oro\Bundle\ProductBundle\DependencyInjection\Configuration;
class ConfigurationProvider
{
    const Allow_FIELD = 'ibnab_pmanager.allow';
    const Logo_FIELD = 'ibnab_pmanager.logo';
    const LogoSize_FIELD = 'ibnab_pmanager.logosize';   
    const TextHeader_FIELD = 'ibnab_pmanager.textheader';
    const TitleHeader_FIELD = 'ibnab_pmanager.titleheader';
    const MarginHeader_FIELD = 'ibnab_pmanager.marginheader';
    const MarginFooter_FIELD = 'ibnab_pmanager.marginfooter';
    
    const EnableMultiTemplate_FIELD = 'ibnab_pmanager.enablemultitemplate';
    const DefaultOrderTemplate_FIELD = 'ibnab_pmanager.defaultordertemplate';
    const DefaultQuoteTemplate_FIELD = 'ibnab_pmanager.defaultquotetemplate';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager, DoctrineHelper $doctrineHelper, $attachmentDir)
    {
        $this->configManager = $configManager;
        $this->doctrineHelper = $doctrineHelper;
        $this->attachmentDir = $attachmentDir;
    }

    /**
     * @return string
     */
    public function getAllowed()
    {
        return $this->configManager->get(self::Allow_FIELD);
    }
    /**
     * @return string
     */
    public function getLogo()
    {       
        $imageId = $this->configManager->get(self::Logo_FIELD);
        $filePath = "";
        if ($imageId && $image = $this->doctrineHelper->getEntityRepositoryForClass(File::class)->find($imageId)) {
            /** @var File $image */
            $filePath = $this->attachmentDir . '/' . $image->getFilename();
        }
        return $filePath;       
    }
    /**
     * @return string
     */
    public function getLogoSize()
    {
        return $this->configManager->get(self::LogoSize_FIELD);
    }
    /**
     * @return string
     */
    public function getTextHeader()
    {
        return $this->configManager->get(self::TextHeader_FIELD);
    }
    /**
     * @return string
     */
    public function getTitleHeader()
    {
        return $this->configManager->get(self::TitleHeader_FIELD);
    }
    /**
     * @return string
     */
    public function getMarginHeader()
    {
        return $this->configManager->get(self::MarginHeader_FIELD);
    }
    /**
     * @return string
     */
    public function getMarginFooter()
    {
        return $this->configManager->get(self::MarginFooter_FIELD);
    }
    /**
     * @return string
     */
    public function getDefaultQuoteTemplate()
    {
        return $this->configManager->get(self::DefaultQuoteTemplate_FIELD);
    }
    /**
     * @return string
     */
    public function getDefaultOrderTemplate()
    {
        return $this->configManager->get(self::DefaultOrderTemplate_FIELD);
    }
    /**
     * @return string
     */
    public function getEnableMultiTemplate()
    {
        return $this->configManager->get(self::EnableMultiTemplate_FIELD);
    }
}
