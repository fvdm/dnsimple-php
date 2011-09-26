<?php
class DNSimple
{
	# API basics
	public $url		= 'https://dnsimple.com';					# Base URL for API calls
	public $http_iface	= '';								# Outbound network interface or IP
	public $http_agent	= 'DNSimple-PHP/1.0.0 (https://github.com/fvdm/dnsimple-php)';	# Outbound user agent string
	public $http_timeout	= 8;								# Connect and wait timeout
	
	# Set raw body, transfer info, request/reponse headers in $this->http
	public $debug		= false;
	
	# API credentials
	protected $username	= '';
	protected $password	= '';
	
	
	###############
	## TEMPLATES ##
	###############
	
	// List DNS templates
	final public function templates_list()
	{
		$list = $this->http_call( 'GET', '/templates' );
		$result = array();
		if( $list[0]['dns_template'] )
		{
			foreach( $list as $tmp )
			{
				$result[ $tmp['dns_template']['short_name'] ] = $tmp['dns_template'];
			}
		}
		return $result;
	}
	
	// Show DNS template
	final public function templates_show( $ref )
	{
		$tmp = $this->http_call( 'GET', '/templates/'. $ref );
		return $tmp['dns_template'] ? $tmp['dns_template'] : false;
	}
	
	// Create DNS template
	final public function templates_create( $arr )
	{
		$vars = array();
		foreach( $arr as $k => $v )
		{
			$vars['dns_template['. $k .']'] = $v;
		}
		$tmp = $this->http_call( 'POST', '/templates', $vars );
		return $tmp['dns_template'] ? $tmp['dns_template'] : false;
	}
	
	// Delete DNS template
	final public function templates_delete( $ref )
	{
		$tmp = $this->http_call( 'DELETE', '/templates/'. $ref );
		return $tmp['status'] == 'deleted' ? true : false;
	}
	
	// Apply DNS template to domain
	final public function templates_apply( $ref, $domain )
	{
		$tmp = $this->http_call( 'POST', '/domains/'. $domain .'/templates/'. $ref .'/apply' );
		return $tmp;
	}
	
	
	## Templates Records
	
	// List DNS template records
	final public function templates_records_list( $ref )
	{
		$result = array();
		$records = $this->http_call( 'GET', '/templates/'. $ref .'/template_records' );
		if( $records[0]['dns_template_record']['id'] )
		{
			foreach( $records as $rec )
			{
				$result[ $rec['dns_template_record']['id'] ] = $rec['dns_template_record'];
			}
		}
		return $result;
	}
	
	// Show DNS template record
	final public function templates_records_show( $ref, $id )
	{
		$rec = $this->http_call( 'GET', '/templates/'. $ref .'/template_records/'. $id );
		return $rec['dns_template_record']['id'] ? $rec['dns_template_record'] : false;
	}
	
	// Create DNS template record
	final public function templates_records_create( $ref, $arr )
	{
		$vars = array();
		foreach( $arr as $k => $v )
		{
			$vars['dns_template_record['. $k .']'] = $v;
		}
		
		$rec = $this->http_call( 'POST', '/templates/'. $ref .'/template_records', $vars );
		return $rec;
	}
	
	
	
	#########
	## DNS ##
	#########
	
	// Update DNS record
	final public function dns_update( $domain, $id, $arr )
	{
		foreach( $arr as $k => $v )
		{
			$vars[ 'record['. $k .']' ] = $v;
		}
		$res = $this->http_call( 'PUT', '/domains/'. $domain .'/records/'. $id, $vars );
		return $res['record']['id'] ? $res['record'] : false;
	}
	
	// Delete DNS record
	final public function dns_delete( $domain, $id )
	{
		$del = $this->http_call( 'DELETE', '/domains/'. $domain .'/records/'. $id );
		return $del['status'] == 'deleted' ? true : true;
	}
	
	// Create DNS record
	// REQ: name, record_type, content
	// OPT: ttl, prio
	final public function dns_create( $domain, $arr )
	{
		foreach( $arr as $k => $v )
		{
			$vars[ 'record['. $k .']' ] = $v;
		}
		$res = $this->http_call( 'POST', '/domains/'. $domain .'/records', $vars );
		return $res['record']['id'] ? $res['record'] : false;
	}
	
	// List DNS records for domain
	final public function dns_list( $domain )
	{
		$dns = $this->http_call( 'GET', '/domains/'. $domain .'/records' );
		if( $dns[0]['record']['id'] )
		{
			foreach( $dns as $rec )
			{
				$records[ $rec['record']['id'] ] = $rec['record'];
			}
			return $records;
		}
		return false;
	}
	
	// Get one DNS record details
	final public function dns_show( $domain, $id )
	{
		$record = $this->http_call( 'GET', '/domains/'. $domain .'/records/'. $id );
		return $record['record']['id'] ? $record['record'] : false;
	}
	
	
	##############
	## CONTACTS ##
	##############
	
	// List contacts
	final public function contacts_list()
	{
		$contacts = $this->http_call( 'GET', '/contacts' );
		if( $contacts[0] )
		{
			foreach( $contacts as $ck => $cd )
			{
				$res[ $cd['contact']['id'] ] = $cd['contact'];
			}
			return $res;
		}
		return false;
	}
	
	// Get one contact details
	final public function contacts_show( $id )
	{
		$contact = $this->http_call( 'GET', '/contacts/'. $id );
		return $contact['contact'] ? $contact['contact'] : false;
	}
	
	// Create contact
	// REQ: first_name, last_name, address1, city, state_province, postal_code, country, email_address, phone
	// OPT: organization_name, job_title, fax, phone_ext, label
	//      organization_name requires job_title
	final public function contacts_create( $arr )
	{
		foreach( $arr as $k => $v )
		{
			$array['contact['. $k .']'] = $v;
		}
		$c = $this->http_call( 'POST', '/contacts', $array );
		return $c['contact']['id'] ? $c['contact'] : false;
	}
	
	// Update contact
	final public function contacts_update( $id, $arr )
	{
		foreach( $arr as $k => $v )
		{
			$array['contact['. $k .']'] = $v;
		}
		$contact = $this->http_call( 'PUT', '/contacts/'. $id, $array );
		return $this->http['success'] == 'yes' ? true : false;
	}
	
	// Delete contact
	final public function contacts_delete( $id )
	{
		$c = $this->http_call( 'DELETE', '/contacts/'. $id );
		return $c['status'] == 'deleted' ? true : false;
	}
	
	// Find contact by field, keyword
	// $field can be like create_contact() + id, created_at, updated_at, user_id
	final public function contacts_find_byField( $field, $keyword )
	{
		$contacts = $this->contacts_list();
		$result = array();
		foreach( $contacts as $id => $contact )
		{
			if( stristr( $contact[ $field ], $keyword ) )
			{
				$result[ $id ] = $contact;
			}
		}
		return $result;
	}
	
	
	#############
	## DOMAINS ##
	#############
	
	// List domains, $simple returns only array with domainnames
	final public function domains_list( $simple=false )
	{
		$domains = $this->http_call( 'GET', '/domains' );
		if( $domains[0]['domain'] )
		{
			foreach( $domains as $dk => $dd )
			{
				$res[ $dd['domain']['name'] ] = $simple ? $dd['domain']['name'] : $dd['domain'];
			}
			return $res;
		}
		return $domains;
	}
	
	final public function domains_register( $domain, $contactID )
	{
		$domain = $this->http_call( 'POST', '/domain_registrations', array(
			'domain[name]'			=>	$domain,
			'domain[registrant_id]'	=>	$contactID
		));
		return $domain;
	}
	
	final public function domains_transfer( $domain, $contactID, $authcode=false )
	{
		$a['domain[name]']			=	$domain;
		$a['domain[registrant_id]']	=	$contactID;
		if( $authcode )
		{
			$a['domain[auth_info]']	=	$authcode;
		}
		$domain = $this->http_call( 'POST', '/domain_transfers', $a );
		return $domain;
	}
	
	// Get domain
	final public function domains_show( $domain )
	{
		$domain = $this->http_call( 'GET', '/domains/'. $domain );
		return $domain['domain'] ? $domain['domain'] : false;
	}
	
	// Create domain
	final public function domains_create( $domain )
	{
		$c = $this->http_call( 'POST', '/domains', array(
			'domain[name]'		=>	$domain
		));
		
		return $c['domain']['id'] ? $c['domain'] : false;
	}
	
	// Delete domain
	final public function domains_delete( $domain )
	{
		$c = $this->http_call( 'DELETE', '/domains/'. $domain );
		return $c['status'] == 'deleted' ? true : false;
	}
	
	// Find domains by keyword
	final public function domains_find( $keyword )
	{
		$domains = $this->domains_list();
		$result = array();
		foreach( $domains as $name => $dom )
		{
			if( stristr( $name, $keyword ) )
			{
				$result[ $name ] = $dom;
			}
		}
		return $result;
	}
	
	// Find domains bt TLD
	final public function domains_find_byTLD( $tld )
	{
		$domains = $this->domains_list();
		$result = array();
		foreach( $domains as $name => $dom )
		{
			if( preg_match( '/\.'. $tld .'$/i', $name ) )
			{
				$result[ $name ] = $dom;
			}
		}
		return $result;
	}
	
	// Find domains by registrant ID
	final public function domains_find_byRegistrantID( $registrant )
	{
		$domains = $this->domains_list();
		$result = array();
		foreach( $domains as $name => $dom )
		{
			if( $dom['registrant_id'] == $registrant )
			{
				$result[ $name ] = $dom;
			}
		}
		return $result;
	}
	
	// Find domains by registrant name
	final public function domains_find_byContactName( $name )
	{
		$result = array();
		$domains = $this->domains_list();
		$contacts = $this->contacts_find_byField( 'last_name', $name );
		
		foreach( $contacts as $cid => $contact )
		{
			foreach( $domains as $name => $dom )
			{
				if( $dom['registrant_id'] == $cid )
				{
					$result[ $name ] = $dom;
				}
			}
		}
		return $result;
	}
	
	
	###############
	## UTILITIES ##
	###############
	
	
	// curl header callback
	final private function http_headers( $c, $headers )
	{
		$headers2 = explode( "\n", $headers );
		foreach( $headers2 as $head )
		{
			$head = trim( $head );
			$head = explode( ': ', $head, 2 );
			$key = trim( $head[0] );
			$val = !empty($head[1]) ? trim( $head[1] ) : '';
			
			if( !empty( $key ) )
			{
				if( empty( $val ) )
				{
					$this->http_responseheaders[] = $head[0];
				}
				else
				{
					$this->http_responseheaders[ $key ] = trim( $val );
				}
			}
		}
		
		# required for callback
		return strlen( $headers );
	}
	
	
	// Talk to API
	final private function http_call( $method='GET', $path, $vars=false )
	{
		# init
		$q = '';
		$this->http_responseheaders = array();
		
		# send headers
		$send_headers[] = 'Accept: application/json';
		
		$c = curl_init();
		$a = array(
			CURLOPT_RETURNTRANSFER		=>	true,
			CURLOPT_TIMEOUT			=>	$this->http_timeout,
			CURLOPT_CONNECTTIMEOUT		=>	$this->http_timeout,
			CURLOPT_USERAGENT		=>	$this->http_agent,
			CURLOPT_CUSTOMREQUEST		=>	$method,
			CURLOPT_FOLLOWLOCATION		=>	true,
			CURLOPT_HTTPAUTH		=>	CURLAUTH_BASIC,
			CURLOPT_USERPWD			=>	$this->username .':'. $this->password,
			CURLOPT_HEADER			=>	false,
			CURLINFO_HEADER_OUT		=>	true,
			CURLOPT_HEADERFUNCTION		=>	array( $this, 'http_headers' ),
			CURLOPT_HTTP_VERSION		=>	CURL_HTTP_VERSION_1_0,
			CURLOPT_HTTPHEADER		=>	$send_headers
		);
		
		# outbound interface (1.2.3.4, eth0, wn1)
		if( !empty( $this->http_iface ) )
		{
			$a[CURLOPT_INTERFACE]		=	$this->http_iface;
		}
		
		# method
		switch( $method )
		{
			case 'POST':
			case 'PUT':
			case 'DELETE':
				$a[CURLOPT_POST]	=	true;
				$a[CURLOPT_POSTFIELDS]	=	$vars;
				break;
			
			case 'GET':
			default:
				if( $vars )
				{
					$q = '?'. http_build_query( $vars );
				}
				break;
		}
		
		# url
		$a[CURLOPT_URL]				=	$this->url . $path . $q;
		
		# execute
		curl_setopt_array( $c, $a );
		$result = curl_exec( $c );
		$this->http['info'] = curl_getinfo( $c );
		
		# validate
		$this->http['success'] = substr( $this->http['info']['http_code'], 0, 1 ) == 2 ? 'yes' : 'no';
		
		# store debug data
		if(debug)
		{
			$this->http['raw'] = $result;
			$this->http['response_headers'] = $this->http_responseheaders;
			$this->http['error_str'] = curl_error( $c );
			$this->http['error_code'] = curl_errno( $c );
			print_r($this->http);
		}
		
		# close connection
		curl_close( $c );
		
		# return decoded body
		$result = json_decode( trim( $result ), true );
		return $result;
	}
}
?>
