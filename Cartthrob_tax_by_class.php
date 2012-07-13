<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

class Cartthrob_tax_by_class extends Cartthrob_tax
{
	// @TODO lang
	public $title = 'tax_by_class';
	public $note = "This requires that items added to the cart include a tax_class set as an item option, otherwise a GLOBAL tax_class will be used if set."; 
	public $settings = array(
		array(
			'type'	=> 'add_to_head',
			'default'	=> '
				<script type="text/javascript">
		
 					jQuery(document).ready(function($){

						$(".tax_class_select").each(function (i) {
							var current = $(this).val(); 
							
							$(this).replaceWith(function() {
								var content = "<select name=\'"+$(this).attr("name")+"\' class=\'tax_class_select\' >"; 
								content += "<option value=\'"+current+"\'>"+current+"</option>"; 
								content += "</select>"; 
							  return content; 
							})
						});
	
						$(".new_class").each(function (i) {
							$(".tax_class_select").append("<option value=\'"+$(this).val()+"\'>"+$(this).val()+"</option>"); 
						});

						$(document).on("change", ".new_class", function(){
 
 							if ($(".tax_class_select option[value="+$(this).val()+"]").length == 0 && $(this).val().length > 0)
							{
								$(".tax_class_select").append("<option value=\'"+$(this).val()+"\'>"+$(this).val()+"</option>"); 
							}
						}); 
					}); 
				</script>
			',
			'name'	=> 'add_to_head',
			'short_name'=> 'tax_by_class_add_to_head'
		),
		array(
			'name' => 'tax_classes',
			'short_name' => 'tax_classes',
			'type' => 'matrix',
			'settings' => array(
				array(
					'name' => 'tax_class',
					'type' =>'text',	
					'short_name' => 'tax_class',
					'attributes'	=> array(
						'class'	=> 'new_class'
					),
				),
			),
 		),
		array(
			'name' => 'tax_by_location_settings',
			'short_name' => 'tax_settings',
			'type' => 'matrix',
			'settings' => array(
				array(
					'name' => 'name',
					'short_name' => 'name',
					'type' =>'text',	
				),
				array(
					'name' => 'tax_percent',
					'short_name' => 'rate',
					'type' => 'text'
				),
				array(
					'name' => 'country',
					'short_name' => 'country',
					'type' => 'select',
					'options' => array(),
					'attributes' => array(
						'class' => 'countries_blank',
					),
				),
				array(
					'name'	=> 'tax_class',
					'short_name'=> 'tax_class',
					'type'	=> 'text',
					'attributes'=> array(
						'class'	=> 'tax_class_select'
					),
				),
				array(
					'name' => 'tax_shipping',
					'short_name' => 'tax_shipping',
					'type' => 'checkbox',
				),
			)
		)
	);
	
	protected $tax_data;
	
	public function get_tax($price)
	{

		$args = func_get_args();
		if (count($args) > 1 && isset($args[1]) && is_object($args[1]))
		{
			$item = $args[1]; 
			
			$prefix = ($this->core->store->config('tax_use_shipping_address')) ? 'shipping_' : '';
			
			foreach ($this->plugin_settings('tax_settings', array()) as $tax_data)
			{
 	 			if ($this->core->cart->customer_info($prefix.'country_code') && $tax_data['country'] == $this->core->cart->customer_info($prefix.'country_code') && $item->item_options('tax_class') == $tax_data['tax_class'])
				{
					$this->tax_data = $tax_data; 
					return $this->core->round($price * $this->tax_rate());
				}
				elseif (isset($tax_data['tax_class']) && $tax_data['tax_class'] == "GLOBAL")
				{
					// this doesn't account for 2 global classes
					$this->tax_data = $tax_data; 
					return $this->core->round($price * $this->tax_rate());
				}
			}
		}
		
		return $this->core->round($price * $this->tax_rate());
 	}
	
	public function tax_name()
	{
		return $this->tax_data('name');
	}
	
	public function tax_rate()
	{
		return $this->core->sanitize_number($this->tax_data('rate'))/100;
	}
	
	public function tax_shipping()
	{
		return (bool) $this->tax_data('tax_shipping');
	}
	
	public function tax_data($key = FALSE)
	{
		if (is_null($this->tax_data))
		{
			$this->tax_data = NULL; 
		}
		
 		if ($key === FALSE)
		{
			return $this->tax_data;
		}
		
		return (isset($this->tax_data[$key])) ? $this->tax_data[$key] : FALSE;
	}
}