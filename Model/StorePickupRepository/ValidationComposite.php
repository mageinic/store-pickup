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
 * @package MageINIC_StorePickup
 * @copyright Copyright (c) 2023. MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

declare(strict_types=1);

namespace MageINIC\StorePickup\Model\StorePickupRepository;

use MageINIC\StorePickup\Api\Data\StorePickupInterface;
use MageINIC\StorePickup\Api\StorePickupRepositoryInterface as StorePickupRepository;
use MageINIC\StorePickup\Model\StorePickup\UrlPathGenerator;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\HydratorInterface;

/**
 * Validates and saves a Store Pickup
 */
class ValidationComposite implements StorePickupRepository
{
    /**
     * @var StorePickupRepository
     */
    private StorePickupRepository $repository;

    /**
     * @var UrlPathGenerator
     */
    protected UrlPathGenerator $urlPathGenerator;

    /**
     * @var HydratorInterface|null
     */
    private ?HydratorInterface $hydrator = null;

    /**
     * @param StorePickupRepository $repository
     * @param UrlPathGenerator $urlPathGenerator
     * @param HydratorInterface|null $hydrator
     */
    public function __construct(
        StorePickupRepository $repository,
        UrlPathGenerator      $urlPathGenerator,
        ?HydratorInterface    $hydrator = null
    ) {
        $this->repository = $repository;
        $this->urlPathGenerator = $urlPathGenerator;
        $this->hydrator = $hydrator ?? ObjectManager::getInstance()->get(HydratorInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function save(StorePickupInterface $storePickup): StorePickupInterface
    {
        $urlKey = $storePickup->getIdentifier();

        if ($urlKey === '' || $urlKey === null) {
            $storePickup->setIdentifier($this->urlPathGenerator->generateUrlKey($storePickup));
        }

        if ($storePickup->getId()) {
            $storePickup = $this->hydrator->hydrate(
                $this->getById((int)$storePickup->getEntityId()),
                $this->hydrator->extract($storePickup)
            );
        }

        return $this->repository->save($storePickup);
    }

    /**
     * @inheritdoc
     */
    public function getById(int $id): StorePickupInterface
    {
        return $this->repository->getById($id);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        return $this->repository->getList($searchCriteria);
    }

    /**
     * @inheritdoc
     */
    public function delete(StorePickupInterface $storePickup): bool
    {
        return $this->repository->delete($storePickup);
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $id): bool
    {
        return $this->repository->deleteById($id);
    }
}
