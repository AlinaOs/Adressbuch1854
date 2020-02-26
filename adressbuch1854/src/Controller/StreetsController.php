<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Streets Controller
 *
 * @property \App\Model\Table\StreetsTable $Streets
 *
 * @method \App\Model\Entity\Street[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class StreetsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
		
		// Take only distinct streets (don't count each orthographical variety as own street)
        $streets = $this->Streets->find()->contain(['Arrondissements'])->where(['id >' => 0])->distinct(['name_old_clean']);
		$this->paginate($streets);

        $this->set(compact('streets'));
    }

    /**
     * View method
     *
     * @param string|null $id Street id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
		$this->loadModel('Persons');
		$this->loadModel('Companies');
		
        $street = $this->Streets->get($id, [
            'contain' => [
            'Arrondissements'],
        ]);
		
		$oldName = $street->name_old_clean;
		
		$sameStreets = $this->Streets->find()->where(['name_old_clean' => $oldName]);
		
		$persons = $this->Persons->find()->contain([
			'LdhRanks',
			'MilitaryStatuses',
			'SocialStatuses',
			'OccupationStatuses',
			'ProfCategories',
			'Addresses.Streets']);
			
		$persons->matching('Addresses.Streets', function($q) use ($oldName){
					return $q->where(['Streets.name_old_clean LIKE' => $oldName]);
				})
				->distinct(['Persons.id']);
			
		$companies = $this->Companies->find()->contain([
			'Addresses.Streets',
			'ProfCategories']);
			
		$companies->matching('Addresses.Streets', function($q) use ($oldName){
					return $q->where(['Streets.name_old_clean LIKE' => $oldName]);
				})
				->distinct(['Companies.id']);

		$this->set(compact('street', 'sameStreets', 'companies', 'persons'));
    }
}
