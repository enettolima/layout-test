<?php
class forms_NewHireForm extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		$states = array(
			'AL' => "Alabama",  
			'AK' => "Alaska",  
			'AZ' => "Arizona",  
			'AR' => "Arkansas",  
			'CA' => "California",  
			'CO' => "Colorado",  
			'CT' => "Connecticut",  
			'DE' => "Delaware",  
			'DC' => "District Of Columbia",  
			'FL' => "Florida",  
			'GA' => "Georgia",  
			'HI' => "Hawaii",  
			'ID' => "Idaho",  
			'IL' => "Illinois",  
			'IN' => "Indiana",  
			'IA' => "Iowa",  
			'KS' => "Kansas",  
			'KY' => "Kentucky",  
			'LA' => "Louisiana",  
			'ME' => "Maine",  
			'MD' => "Maryland",  
			'MA' => "Massachusetts",  
			'MI' => "Michigan",  
			'MN' => "Minnesota",  
			'MS' => "Mississippi",  
			'MO' => "Missouri",  
			'MT' => "Montana",
			'NE' => "Nebraska",
			'NV' => "Nevada",
			'NH' => "New Hampshire",
			'NJ' => "New Jersey",
			'NM' => "New Mexico",
			'NY' => "New York",
			'NC' => "North Carolina",
			'ND' => "North Dakota",
			'OH' => "Ohio",  
			'OK' => "Oklahoma",  
			'OR' => "Oregon",  
			'PA' => "Pennsylvania",  
			'RI' => "Rhode Island",  
			'SC' => "South Carolina",  
			'SD' => "South Dakota",
			'TN' => "Tennessee",  
			'TX' => "Texas",  
			'UT' => "Utah",  
			'VT' => "Vermont",  
			'VA' => "Virginia",  
			'WA' => "Washington",  
			'WV' => "West Virginia",  
			'WI' => "Wisconsin",  
			'WY' => "Wyoming");
		
		$stores = array_merge(array(''), $options['stores']);
		$emplTypes = array('', 'Full', 'Part', 'Seasonal', 'Temp');
		$payTypes = array('', 'Hourly', 'Salary');
		$jobs = array('', 'Store Manager', 'MIT', 'Assistant Manager', 'Sales Associate', 'Other');
		$this->setName('new_hire');
		
		$store = new Zend_Form_Element_Select('store_code');
		$store
			->setLabel('Store')
			->setMultiOptions($stores)
			->setRequired(true)
			->addValidator('NotEmpty');
		
		$emplType = new Zend_Form_Element_Select('empl_type');
		$emplType
			->setLabel('Employee Type')
			->setMultiOptions($emplTypes)
			->setRequired(true)
			->addValidator('NotEmpty');
			
		$firstName = new Zend_Form_Element_Text('first_name');
		$firstName
			->setLabel('First Name')
			->setRequired(true)
			->addValidator('NotEmpty');
		
		$mi = new Zend_Form_Element_Text('mi');
		$mi->setLabel('MI');
			
		$lastName = new Zend_Form_Element_Text('last_name');
		$lastName
			->setLabel('Last Name')
			->setRequired(true)
			->addValidator('NotEmpty');
			
		$address = new Zend_Form_Element_Text('address');
		$address
			->setLabel('Address')
			->setRequired(true)
			->addValidator('NotEmpty');
			
		$address2 = new Zend_Form_Element_Text('address2');
		$address2
			->setLabel('Address2')
			->setRequired(true)
			->addValidator('NotEmpty');
			
		$zip = new Zend_Form_Element_Text('zip');
		$zip
			->setLabel('Zip')
			->setRequired(true)
			->addValidator('NotEmpty');
			
		$hireDate = new Zend_Form_Element_Text('hire_date');
		$hireDate
			->setLabel('Date of Hire')
			->setRequired(true)
			->addValidator('NotEmpty');
			
		$birthDate = new Zend_Form_Element_Text('birth_date');
		$birthDate
			->setLabel('Date of Birth')
			->setRequired(true)
			->addValidator('NotEmpty');	
			
		$married = new Zend_Form_Element_Checkbox('marital_status');
		$married
			->setLabel('Married?');
			
		$ssn = new Zend_Form_Element_Text('ssn');
		$ssn
			->setLabel('SSN')
			->setRequired(true)
			->addValidator('StringLength', 9);
			
		$payType = new Zend_Form_Element_Select('pay_type');
		$payType
			->setLabel('Pay Type')
			->setMultiOptions($payTypes)
			->setRequired(true)
			->addValidator('NotEmpty');
			
		$payRate = new Zend_Form_Element_Text('pay_rate');
		$payRate
			->setLabel('Pay Rate')
			->setRequired(true)
			->addValidator('NotEmpty');
			
		$job = new Zend_Form_Element_Select('job_id');
		$job
			->setLabel('Position')
			->setMultiOptions($jobs)
			->setRequired(true)
			->addValidator('NotEmpty');
			
		$state = new Zend_Form_Element_Select('state');
		$state
			->setLabel('State')
			->setMultiOptions($states)
			->setRequired(true)
			->addValidator('NotEmpty');
			
		$city = new Zend_Form_Element_Text('city');
		$city
			->setLabel('City')
			->setRequired(true)
			->addValidator('NotEmpty');

		$sex = new Zend_Form_Element_Select('sex');
		$sex
			->setLabel('Sex')
			->setMultiOptions(array('M' => 'Male', 'F' => 'Female'))
			->setRequired(true)
			->addValidator('NotEmpty');
			
		$submit = new Zend_Form_Element_Submit('create_newhire');
		$submit
			->setLabel('Save New Hire')
			->setDecorators(array( 
			'ViewHelper',
			array('HtmlTag', array('tag' => 'div'))));
			
		$this->addElements(array(
			$emplType, $firstName, $mi,
			$lastName, $address, $address2,
			$hireDate, $married, $ssn,
			$payType, $payRate, $job,
			$state, $city, $sex,
			$zip, $birthDate, $store));
			
		$this->addDisplayGroup(array(
			'first_name', 'mi', 'last_name',  
			'sex', 'marital_status',
			'address', 'address2', 'city', 
			'state', 'zip', 'birth_date'), 'Personal');
		
		$this->addDisplayGroup(array(
			'hire_date', 'empl_type', 'job_id', 
			'ssn', 'store_code', 'pay_type', 'pay_rate'), 'Employment');
		
		$this->addElement($submit);
	}
	
	function getData() {
		
	}
}