<?php

class InvoiceEvent
{
	const FR_CODES = [
		200 => 'Déposée',
		201 => 'Émise par la plateforme',
		202 => 'Reçue par la plateforme',
		203 => 'Mise à disposition du destinataire par la plateforme',
		204 => 'Reçu par le destinataire',
		205 => 'Approuvée',
		206 => 'Approuvée partiellement',
		207 => 'En litige',
		208 => 'Suspendue',
		209 => 'Complétée',
		210 => 'Refusée',
		211 => 'Paiement transmis',
		212 => 'Encaissée',
		213 => 'Rejetée',
		501 => 'Irrecevable',
	];
}