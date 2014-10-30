<?php
	/** nelíbí se mi to, ale $root je nutné zlo, které vyžaduje edde2 bundle - vypočítá se z něj adresářová struktura aplikace :| */
	return require('phar://'.($root = __DIR__.'/..').'/lib/edde2.bundle.phar');
