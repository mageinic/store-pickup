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

namespace MageINIC\StorePickup\Controller\Adminhtml\StorePickup;

use Exception;
use MageINIC\StorePickup\Controller\Adminhtml\StorePickup;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for Save Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends StorePickup implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageINIC_StorePickup::save';

    /**
     * Execute action based on request and return result
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute(): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            if (empty($data['entity_id'])) {
                $data['entity_id'] = null;
            }

            $data = $this->filterData($data);

            $model = $this->storePickupFactory->create();
            $id = $this->getRequest()->getParam('entity_id');

            try {
                if ($id) {
                    $model = $this->storePickupRepository->getById($id);
                }
            } catch (LocalizedException|Exception $e) {
                $this->messageManager->addErrorMessage(__('This Store no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData($data);

            try {
                $this->storePickupRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the Store.'));
                $this->dataPersistor->clear('mageinic_store_pickup');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getEntityId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong.'));
            }

            $this->dataPersistor->set('mageinic_store_pickup', $data);
            $id = $this->getRequest()->getParam('entity_id');
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     *  Filter store pickup data
     *
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function filterData(array $data): array
    {
        if (isset($data['image']) && is_array($data['image'])) {
            if (!empty($data['image']['delete'])) {
                $data['image'] = null;
            } else {
                if (isset($data['image'][0]['name']) && isset($data['image'][0]['tmp_name'])) {
                    $data['image'] = $data['image'][0]['name'];
                    try {
                        $this->imageUploader->moveFileFromTmp($data['image']);
                    } catch (FileSystemException|LocalizedException $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }
                } else {
                    unset($data['image']);
                }
            }
        }

        if (!empty($data['schedule'])) {
            $storeTime = $this->serialize->serialize($data['schedule']);
            $data['schedule'] = $storeTime;
        }

        if (!empty($data['state_id'])) {
            $region = $this->helperData->getRegionByCode($data['state_id']);
            $data['region'] = $region;
        }

        return $data;
    }
}
