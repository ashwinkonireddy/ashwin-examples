<?php
//namespace name;
//include 'name1.php';
use hello as bob;

echo __NAMESPACE__;

function tryme(){
	echo "Hello Me";
}

\tryme();
\bob\tryme();