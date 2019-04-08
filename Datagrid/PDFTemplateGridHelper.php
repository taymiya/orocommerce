<?php

namespace Ibnab\Bundle\PmanagerBundle\Datagrid;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\EntityBundle\Grid\GridHelper as BaseGridHelper;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;

class PDFTemplateGridHelper extends BaseGridHelper
{
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * Constructor
     *
     * @param EntityProvider      $entityProvider
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityProvider $entityProvider, TranslatorInterface $translator)
    {
        parent::__construct($entityProvider);
        $this->translator = $translator;
    }

    /**
     * Returns callback for configuration of grid/actions visibility per row
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            if ($record->getValue('isSystem')) {
                return array('delete' => false);
            }
        };
    }
    /**
     * Returns callback for configuration of grid/actions visibility per row
     *
     * @return callable
     */
    public function getActionConfigurationClosureLogs()
    {
          return array('update' => false);
    }
    /**
     * Returns email template type choice list
     *
     * @return array
     */
    public function getTypeChoices()
    {
        return array_flip([
            'html' => 'ibnab.pmanager.pdftemplate.datagrid.template.filter.type.html',
            'txt'  => 'ibnab.pmanager.pdftemplate.datagrid.template.filter.type.txt'
        ]);
    }
    /**
     * Returns side choice list
     *
     * @return array
     */
    public function getSides()
    {
        return array_flip([
            'backend' => 'Backend',
            'frontend'  => 'Frontend'
        ]);
    }
    /**
     * Returns type choice list
     *
     * @return array
     */
    public function getTypes()
    {
        return array_flip([
            'unique' => 'Unique',
            'batch-zip'  => 'Batch Zip',
            'batch-one'  => 'Batch One',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityNames()
    {
        $result            = [];
        $result['_empty_'] = $this->translator->trans('ibnab.pmanager.pdftemplate.datagrid.template.filter.entityName.empty');

        $result = array_merge($result, parent::getEntityNames());

        return array_flip($result);
    }
}
