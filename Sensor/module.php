<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/Zigbee2DeCONZHelper.php';

class Z2DSensor extends IPSModule
{
    use Zigbee2DeCONZHelper;

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{9013F138-F270-C396-09D6-43368E390C5F}');

        $this->RegisterPropertyString('DeviceID', "");
        $this->RegisterPropertyString('DeviceType', "sensors");
#	-----------------------------------------------------------------------------------
        $this->RegisterAttributeInteger("State", 0);
        $this->RegisterAttributeInteger("LastUpdated", 0);
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->RegisterMessage($this->InstanceID, IM_CHANGESTATUS);
        $this->RegisterMessage(@IPS_GetInstance($this->InstanceID)['ConnectionID'], IM_CHANGESTATUS);

		@$this->GetStateDeconz();
			
#		Filter setzen
		$this->SetReceiveDataFilter('.*'.preg_quote('\"uniqueid\":\"').$this->ReadPropertyString("DeviceID").preg_quote('\"').'.*');
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        switch ($Message) {
            case IM_CHANGESTATUS:
				if($SenderID == @IPS_GetInstance($this->InstanceID)['ConnectionID']){
					if($Data[0] >= 200)$Data[0] = 215;
					$state = max($Data[0], $this->ReadAttributeInteger("State"));
					if($state <> $this->GetStatus())$this->SetStatus($state);
				}
				if($SenderID == $this->InstanceID){
					if($Data[0] == 102) $this->GetStateDeconz();
				}
                break;
        }
    }

    public function ReceiveData($JSONString)
    {
        $Buffer = json_decode($JSONString)->Buffer;
        $this->SendDebug('Received', $Buffer, 0);

        $data = json_decode(utf8_decode($Buffer));
	    if (property_exists($data, 'state')) {
			$Payload = $data->state;
			if(strtotime($Payload->lastupdated." UTC") <> $this->ReadAttributeInteger("LastUpdated")){
				$this->WriteAttributeInteger("LastUpdated", strtotime($Payload->lastupdated." UTC"));

				if (property_exists($Payload, 'buttonevent')) {
					$this->RegisterVariableInteger('Z2D_Event', $this->Translate('Event'), '');
					$this->SetValue('Z2D_Event', $Payload->buttonevent);

					$button = (int)($Payload->buttonevent / 1000);
					$state  = $Payload->buttonevent % 1000;
					$this->RegisterVariableInteger('Z2D_Button_'.$button, $this->Translate('Button')." ".$button, 'ButtonEvent.Z2D');

					if (!IPS_VariableProfileExists('ButtonEvent.Z2D')) {
						IPS_CreateVariableProfile('ButtonEvent.Z2D', 1);
						IPS_SetVariableProfileIcon('ButtonEvent.Z2D', 'Power');
						IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 0, $this->Translate('Initial Press'), '',-1);
						IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 1, $this->Translate('Hold'), '',-1);
						IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 2, $this->Translate('Release after press'), '',-1);
						IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 3, $this->Translate('Release after hold'), '',-1);
						IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 4, $this->Translate('Double press'), '',-1);
						IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 5, $this->Translate('Triple press'), '',-1);
						IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 6, $this->Translate('Quadruple press'), '',-1);
						IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 7, $this->Translate('Shake'), '',-1);
						IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 8, $this->Translate('Drop'), '',-1);
						IPS_SetVariableProfileAssociation('ButtonEvent.Z2D', 9, $this->Translate('Tilt'), '',-1);
						IPS_SetVariableProfileAssociation('ButtonEvent.Z2D',10, $this->Translate('Many press'), '',-1);
					}
					SetValue($this->GetIDForIdent('Z2D_Button_'.$button), $state);
				}
				if (property_exists($Payload, 'carbonmonoxide')) {
					$this->RegisterVariableBoolean('Z2D_Carbonmonoxide', $this->Translate('Carbonmonoxide'), '~Alert');
					SetValue($this->GetIDForIdent('Z2D_Carbonmonoxide'), $Payload->carbonmonoxide);
				}
				if (property_exists($Payload, 'dark')) {
					$this->RegisterVariableBoolean('Z2D_dark', $this->Translate('dark'), '~Switch');
					SetValue($this->GetIDForIdent('Z2D_dark'), $Payload->dark);
				}
				if (property_exists($Payload, 'fire')) {
					$this->RegisterVariableBoolean('Z2D_fire', $this->Translate('Fire'), '~Alert');
					SetValue($this->GetIDForIdent('Z2D_fire'), $Payload->fire);
				}
				if (property_exists($Payload, 'daylight')) {
					$this->RegisterVariableBoolean('Z2D_daylight', $this->Translate('Daylight'), '~Switch');
					SetValue($this->GetIDForIdent('Z2D_daylight'), $Payload->daylight);
				}
				if (property_exists($Payload, 'lowbattery')) {
					$this->RegisterVariableBoolean('Z2D_lowbattery', $this->Translate('Battery'), '~Battery');
					SetValue($this->GetIDForIdent('Z2D_lowbattery'), $Payload->lowbattery);
				}
				if (property_exists($Payload, 'presence')) {
					$this->RegisterVariableBoolean('Z2D_presence', $this->Translate('Presence'), '~Presence');
					SetValue($this->GetIDForIdent('Z2D_presence'), $Payload->presence);
				}
				if (property_exists($Payload, 'open')) {
					$this->RegisterVariableBoolean('Z2D_open', $this->Translate('open'), '~Window');
					SetValue($this->GetIDForIdent('Z2D_open'), $Payload->open);
				}
				if (property_exists($Payload, 'on')) {
					$this->RegisterVariableBoolean('Z2D_on', $this->Translate('on'), '~Switch');
					SetValue($this->GetIDForIdent('Z2D_on'), $Payload->on);
				}
				if (property_exists($Payload, 'tampered')) {
					$this->RegisterVariableBoolean('Z2D_tampered', $this->Translate('tampered'), '~Alert');
					SetValue($this->GetIDForIdent('Z2D_tampered'), $Payload->tampered);
				}
				if (property_exists($Payload, 'water')) {
					$this->RegisterVariableBoolean('Z2D_water', $this->Translate('Water'), '~Alert');
					SetValue($this->GetIDForIdent('Z2D_water'), $Payload->water);
				}
				if (property_exists($Payload, 'vibration')) {
					$this->RegisterVariableBoolean('Z2D_vibration', $this->Translate('Vibration'), '~Alert');
					SetValue($this->GetIDForIdent('Z2D_vibration'), $Payload->vibration);
				}
				if (property_exists($Payload, 'humidity')) {
					$this->RegisterVariableFloat('Z2D_humidity', $this->Translate('Humidity'), '~Humidity.F');
					SetValue($this->GetIDForIdent('Z2D_humidity'), $Payload->humidity / 100.0);
				}
				if (property_exists($Payload, 'lux')) {
					$this->RegisterVariableFloat('Z2D_lux', $this->Translate('Illumination'), '~Illumination.F');
					SetValue($this->GetIDForIdent('Z2D_lux'), $Payload->lux);
				}
				if (property_exists($Payload, 'lightlevel')) {
					$this->RegisterVariableFloat('Z2D_lightlevel', $this->Translate('Illumination'), '~Illumination.F');
					SetValue($this->GetIDForIdent('Z2D_lightlevel'), $Payload->lightlevel);
				}
				if (property_exists($Payload, 'pressure')) {
					$this->RegisterVariableFloat('Z2D_pressure', $this->Translate('Airpressure'), '~AirPressure.F');
					SetValue($this->GetIDForIdent('Z2D_pressure'), $Payload->pressure);
				}
				if (property_exists($Payload, 'temperature')) {
					$this->RegisterVariableFloat('Z2D_temperature', $this->Translate('Temperature'), '~Temperature');
					SetValue($this->GetIDForIdent('Z2D_temperature'), $Payload->temperature / 100.0);
				}
				if (property_exists($Payload, 'consumption')) {
					$this->RegisterVariableFloat('Z2D_consumption', $this->Translate('Consumption'), '~Electricity');
					SetValue($this->GetIDForIdent('Z2D_consumption'), round($Payload->consumption / 1000 ,3));
				}
				if (property_exists($Payload, 'power')) {
					$this->RegisterVariableFloat('Z2D_power', $this->Translate('Power'), '~Watt.14490');
					SetValue($this->GetIDForIdent('Z2D_power'), $Payload->power);
				}
				if (property_exists($Payload, 'voltage')) {
					$this->RegisterVariableFloat('Z2D_voltage', $this->Translate('Voltage'), '~Volt');
					SetValue($this->GetIDForIdent('Z2D_voltage'), $Payload->voltage);
				}
				if (property_exists($Payload, 'current')) {
					$this->RegisterVariableFloat('Z2D_current', $this->Translate('Current'), 'Ampere.Z2D');

					if (!IPS_VariableProfileExists('Ampere.Z2D')) {
						IPS_CreateVariableProfile('Ampere.Z2D', 2);
						IPS_SetVariableProfileIcon('Ampere.Z2D', 'Electricity');
						IPS_SetVariableProfileText('Ampere.Z2D', '', ' A');
						IPS_SetVariableProfileDigits('Ampere.Z2D', 2);
					}

					SetValue($this->GetIDForIdent('Z2D_current'), round($Payload->current / 1000 ,3));
				}
			}
		}

	    if (property_exists($data, 'config')) {
			$Payload = $data->config;
			if (property_exists($Payload, 'battery')) {
			    $this->RegisterVariableInteger('Z2D_Battery', $this->Translate('Battery'), '~Battery.100');
			    SetValue($this->GetIDForIdent('Z2D_Battery'), $Payload->battery);
			}
			if (property_exists($Payload, 'reachable')) {
				if($Payload->reachable){
					if($this->ReadAttributeInteger("State") <> 102)$this->WriteAttributeInteger("State", 102);
				}else{
					if($this->ReadAttributeInteger("State") <> 215)$this->WriteAttributeInteger("State", 215);
				}
			}
			if (property_exists($Payload, 'temperature')) {
			    $this->RegisterVariableFloat('Z2D_temperature', $this->Translate('Temperature'), '~Temperature');
			    SetValue($this->GetIDForIdent('Z2D_temperature'), $Payload->temperature / 100.0);
			}
			if (property_exists($Payload, 'heatsetpoint')) {
			    $this->RegisterVariableFloat('Z2D_heatsetpoint', $this->Translate('Heat Setpoint'), '~Temperature');
	            $this->EnableAction('Z2D_heatsetpoint');
			    SetValue($this->GetIDForIdent('Z2D_heatsetpoint'), $Payload->heatsetpoint / 100.0);
			}
			if (property_exists($Payload, 'offset')) {
			    $this->RegisterVariableFloat('Z2D_offset', $this->Translate('Offset'), '~Temperature');
	            $this->EnableAction('Z2D_offset');
			    SetValue($this->GetIDForIdent('Z2D_offset'), $Payload->offset / 100.0);
			}
			if (property_exists($Payload, 'sensitivity')) {
			    $this->RegisterVariableInteger('Z2D_sensitivity', $this->Translate('Sensitivity'), 'Sensitivity.Z2D');
				$this->EnableAction('Z2D_sensitivity');

				if (!IPS_VariableProfileExists('Sensitivity.Z2D')) {
					IPS_CreateVariableProfile('Sensitivity.Z2D', 1);
					IPS_SetVariableProfileAssociation('Sensitivity.Z2D', 0, $this->Translate('Low'), '',-1);
					IPS_SetVariableProfileAssociation('Sensitivity.Z2D', 1, $this->Translate('Medium'), '',-1);
					IPS_SetVariableProfileAssociation('Sensitivity.Z2D', 2, $this->Translate('High'), '',-1);
				}
			    $this->SetValue('Z2D_sensitivity', $Payload->sensitivity);
			}
	    }
    }
}
