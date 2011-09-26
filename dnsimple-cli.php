#!/usr/bin/php
<?php
define('debug', false);

if(debug)
{
	ini_set('display_errors', 1);
	date_default_timezone_set( 'Europe/Amsterdam');
}
error_reporting( E_ALL ^ E_NOTICE );

# REQUIRED !!
require 'dnsimple.php';

$d = new DNSimple;
$d->debug = false;

switch( $argv[1] )
{
	# TEMPLATES
	case 'templates':
		switch( $argv[2] )
		{
			case 'list':	$r = $d->templates_list();				break;	# LIST
			case 'show':	$r = $d->templates_show( $argv[3] );			break;	# SHOW
			case 'delete':	$r = $d->templates_delete( $argv[3] );			break;	# DELETE
			case 'apply':	$r = $d->templates_apply( $argv[3], $argv[4] );		break;	# APPLY
			
			# ADD
			case 'add':
				$r = $d->templates_create( array(
					'name'		=>	$argv[3],
					'short_name'	=>	$argv[4],
					'description'	=>	(string) $argv[5]
				));
				break;
				
			# RECORDS
			case 'records':
				switch( $argv[3] )
				{
					case 'list':	$r = $d->templates_records_list( $argv[4] );		break;	# LIST
					case 'show':	$r = $d->templates_records_show( $argv[4], $argv[5] );	break;	# SHOW
					
					# ADD
					case 'add':
						$a = array(
							'name'		=>	$argv[5],
							'record_type'	=>	$argv[6],
							'ttl'		=>	3600
						);
						if( $argv[6] == 'MX' )
						{
							$a['content']	=	$argv[8];
							$a['prio']	=	$argv[7];
						}
						else
						{
							$a['content']	=	$argv[7];
						}
						$r = $d->templates_records_create( $argv[4], $a );
						break;
				}
				break;
		}
		break;
	
	# DNS
	case 'dns':
		switch( $argv[2] )
		{
			case 'delete':	$r = $d->dns_delete( $argv[3], $argv[4] );	break;	# DELETE
			case 'show':	$r = $d->dns_show( $argv[3], $argv[4] );	break;	# SHOW
			case 'list':	$r = $d->dns_list( $argv[3] );			break;	# LIST
			
			# UPDATE
			case 'update':
				$r = $d->dns_update( $argv[3], $argv[4], array(
					'name'		=>	$argv[5],
					'record_type'	=>	$argv[6],
					'content'	=>	$argv[7],
					'prio'		=>	$argv[8] ? $argv[8] : '',
					'ttl'		=>	3600
				));
				break;
			
			# ADD
			case 'add':
				$r = $d->dns_create( $argv[3], array(
					'name'		=>	$argv[4],
					'record_type'	=>	$argv[5],
					'content'	=>	$argv[6],
					'prio'		=>	$argv[7] ? $argv[7] : '',
					'ttl'		=>	3600
				));
				break;
			
			# DATE
			case 'date':
				$r = $d->dns_create( $argv[3], array(
					'name'		=>	'test'. date('His'),
					'record_type'	=>	'TXT',
					'content'	=>	date('r')
				));
				break;
		}
		break;
		
	# DOMAINS
	case 'domains':
		switch( $argv[2] )
		{
			case 'add':	$r = $d->domains_create( $argv[3] );	break;	# ADD
			case 'delete':	$r = $d->domains_delete( $argv[3] );	break;	# DELETE
			case 'list':	$r = $d->domains_list();		break;	# LIST
			case 'show':	$r = $d->domains_show( $argv[3] );	break;	# SHOW
			
			# FIND
			case 'find':
				if( $argv[3] == 'contact' )
				{
					# by contact name
					$r = $d->domains_find_byContactName( $argv[4] );
				}
				else
				{
					# by keyword
					$r = $d->domains_find( $argv[3] );
				}
				break;
		}
		break;
	
	# CONTACTS
	case 'contacts':
		switch( $argv[2] )
		{
			case 'list':	$r = $d->contacts_list();		break;	# LIST
			case 'delete':	$r = $d->contacts_delete( $argv[3] );	break;	# DELETE
			case 'show':	$r = $d->contacts_show( $argv[3] );	break;	# SHOW
			
			# FIND
			case 'find':
				$field = $argv[4] ? $argv[3] : 'last_name';
				$keyword = $argv[4] ? $argv[4] : $argv[3];
				$r = $d->contacts_find_byField( $field, $keyword );
				break;
			
			# ADD
			case 'add':
				//$r = $d->contacts_create( array() );
				break;
			
			# UPDATE
			case 'update':
				//$r = $d->contacts_update( $argv[3], array() );
				break;
		}
		break;
	
	# HELP
	case 'help';
	default:
		echo "\nUSAGE test.php COMMAND\n";
		echo "\n";
		echo "	domains		list\n";
		echo "			add|delete|show domain.tld\n";
		echo "			find keyword\n";
		echo "			find contact name\n";
		echo "\n";
		echo "	templates	list\n";
		echo "			delete|show name\n";
		echo "			apply name domain.tld\n";
		echo "			add \"title\" shortname \"description\"\n";
		echo "			records list name\n";
		echo "			records show name id\n";
		echo "			records add name dnsname type [prio] content\n";
		echo "\n";
		echo "	dns		list domain.tld\n";
		echo "			delete|show domain.tld id\n";
		echo "			add domain.tld dnsname type content prio\n";
		echo "			update domain.tld id dnsname type content [prio]\n";
		echo "\n";
		echo "	contacts	list\n";
		echo "			delete|show id\n";
		echo "			find [field] keyword\n";
		echo "				[field] can be first_name, last_name, address1, city,\n";
		echo "				state_province, postal_code, country, email_address, phone\n";
}


// dump
echo "\n";
print_r($r);
if( $d->debug )
{
	echo "\n";
	print_r($d->http);
}
echo "\n";
?>