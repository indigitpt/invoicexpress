<?php
require('../vendor/autoload.php');
error_reporting(E_ALL);

use Faker\Provider\pt_PT\Address;
use Faker\Provider\pt_PT\Company;
use Faker\Provider\pt_PT\Internet;
use Faker\Provider\pt_PT\Person;
use Faker\Provider\pt_PT\PhoneNumber;
use InvoiceXpress\Auth;

# Replace this with your account ID and API Key for testing
$auth = new Auth('YOUR_ACCOUNT_NAME', 'YOUR_API_KEY');

# Dummy Data and ids here
$account_id = '';
$account_id_2 = '';
$client_id = '';
$tax_id = '';
$invoice_id = '';
$receipt_id = '';


$faker = new \Faker\Generator();
$faker->addProvider(new Person($faker));
$faker->addProvider(new Address($faker));
$faker->addProvider(new Company($faker));
$faker->addProvider(new PhoneNumber($faker));
$faker->addProvider(new Internet($faker));