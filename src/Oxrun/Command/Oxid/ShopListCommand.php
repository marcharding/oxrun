<?php
/**
 * Created by PhpStorm.
 * User: tobi
 * Date: 2019-02-20
 * Time: 01:07
 */

namespace Oxrun\Command\Oxid;

use Oxrun\Traits\NeedDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ShopListCommands
 * @package Oxrun\Command\Misc
 */
class ShopListCommand extends Command implements \Oxrun\Command\EnableInterface
{
    use NeedDatabase;

    protected function configure()
    {
        $this->setName('oxid:shops')
            ->setDescription('Lists the shops');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);

        /** @var \oxShopList $oxShopList */
        $oxShopList = oxNew(\OxidEsales\Eshop\Application\Model\ShopList::class);
        $oxShopList->getAll();

        $isVerbose = $output->getVerbosity() & OutputInterface::VERBOSITY_VERBOSE ||
                     $output->getVerbosity() & OutputInterface::VERBOSITY_VERY_VERBOSE ||
                     $output->getVerbosity() & OutputInterface::VERBOSITY_DEBUG;

        if ($isVerbose) {
            $this->displayVerbose($oxShopList, $table);
        } else {
            $this->displayNormal($oxShopList, $table);
        }

        $table->render();
    }

    /**
     * @param $oxShopList
     * @param Table $table
     */
    protected function displayNormal($oxShopList, Table $table)
    {
        $headers = [
            'ShopId',
            'Shop name',
            'Active',
            'Productive',
            'Url',
            'SEO active',
        ];

        $table->setHeaders($headers);

        foreach ($oxShopList as $oxShop) {
            $row = [
                $oxShop->oxshops__oxid->rawValue,
                $oxShop->oxshops__oxname->rawValue,
                $oxShop->oxshops__oxactive->rawValue ? 'yes' : 'no',
                $oxShop->oxshops__oxproductive->rawValue ? 'yes' : 'no',
                $oxShop->oxshops__oxurl->rawValue,
                $oxShop->oxshops__oxseoactive->rawValue ? 'yes' : 'no',
            ];
            $table->addRow($row);
        }
    }

    /**
     * @param \oxBase $oxShop
     * @param Table $table
     */
    protected function displayVerbose($oxShopList, Table $table)
    {
        $headers = ['Field name'];
        $row = [];

        $fieldnames = $oxShopList[1]->getFieldNames();
        foreach ($fieldnames as $fieldname) {
            $row[$fieldname] = [$fieldname];
        }

        foreach ($oxShopList as $oxShop) {
            $headers[] = "ShopId: " . $oxShop->getId();

            foreach ($fieldnames as $fieldname) {
                $row[$fieldname][] = $oxShop->getFieldData($fieldname);
            }
        }
        $table->setHeaders($headers);
        $table->addRows(array_values($row));
    }
}
