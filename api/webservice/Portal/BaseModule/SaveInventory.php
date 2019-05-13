<?php
/**
 * The file contains a the SaveInventory class.
 *
 * @package   Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

/**
 * Saving data to the inventory module.
 */
class SaveInventory extends \Api\Core\BaseAction
{
	/**
	 * {@inheritdoc}
	 */
	public $allowedMethod = ['POST'];

	/**
	 * Create inventory record.
	 *
	 * @return array
	 */
	public function post(): array
	{
		$moduleName = $this->controller->request->getModule();
		$inventory = $this->controller->request->getArray('inventory');
		$recordModel = \Vtiger_Record_Model::getCleanInstance($moduleName);
		$recordModel->set('subject', $moduleName . '/' . date('Y-m-d'));
		$recordModel->initInventoryData(
			(new \Api\Portal\BaseModel\SaveInventory($moduleName, $inventory))->getInventoryData(),
			false
		);
		$recordModel->save();
		return [
			'id' => $recordModel->getId(),
		];
	}
}