<?php
/**
 * MageINIC
 * Copyright (C) 2023. MageINIC <support@mageinic.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://opensource.org/licenses/gpl-3.0.html.
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category MageINIC
 * @package MageINIC_<ModuleName>
 * @copyright Copyright (c) 2023. MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

namespace MageINIC\StorePickup\Observer;

use Exception;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

/**
 * Class for ConfigChange
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigChange implements ObserverInterface
{
    public const REQUEST_PATH = 'store_pickup';

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var WriterInterface
     */
    protected WriterInterface $configWriter;

    /**
     * @var UrlRewriteFactory
     */
    protected UrlRewriteFactory $urlRewriteFactory;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * ConfigChange constructor.
     *
     * @param RequestInterface $request
     * @param WriterInterface $configWriter
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RequestInterface      $request,
        WriterInterface       $configWriter,
        UrlRewriteFactory     $urlRewriteFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->configWriter = $configWriter;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute method for event observer
     *
     * @param EventObserver $observer
     * @return $this
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function execute(EventObserver $observer): ConfigChange
    {
        $faqParams = $this->request->getParam('groups');
        if (!empty($faqParams['store_list_view']['fields']['frontend_url'])) {
            $faqUrlVal = $faqParams['store_list_view']['fields']['frontend_url'];
            $urlKey = str_replace(' ', '-', $faqUrlVal['value']);
            $filterUrlKey = preg_replace('/[^A-Za-z0-9\-]/', '', $urlKey);
            $this->configWriter->save('store_pickup/store_list_view/frontend_url', $filterUrlKey);
            $stores = $this->storeManager->getStores();
            foreach ($stores as $store) {
                $urlRewriteModel = $this->urlRewriteFactory->create();
                $rewriteCollection = $urlRewriteModel->getCollection()
                    ->addFieldToFilter('request_path', self::REQUEST_PATH)
                    ->addFieldToFilter('store_id', $store->getId())
                    ->getFirstItem();
                $urlRewriteModel->load($rewriteCollection->getId());
                if ($filterUrlKey == self::REQUEST_PATH) {
                    if ($urlRewriteModel->getId()) {
                        $urlRewriteModel->delete();
                    }
                } else {
                    $urlRewriteModel->setStoreId($store->getId());
                    $urlRewriteModel->setTargetPath($filterUrlKey);
                    $urlRewriteModel->setRequestPath(self::REQUEST_PATH);
                    $urlRewriteModel->setredirectType(301);
                    $urlRewriteModel->save();
                }
            }
        }

        return $this;
    }
}
