# Access control adapters

Don't use this in production. It's an early alpha release.

## Requirements

- This plugin needs [li3_behaviors](https://github.com/jails/li3_behaviors) (only if you intend to use the DbAcl adapter).
- This plugin needs [li3_tree](https://github.com/jails/li3_tree) (only if you intend to use the DbAcl adapter).
- This plugin needs [li3_fixtures](https://github.com/UnionOfRAD/li3_fixtures) (only if you intend to run tests).
- This plugin needs [li3_sqltools](https://github.com/UnionOfRAD/li3_sqltools) (only if you intend to run tests).

## Installation

Checkout the code to either of your library directories:

```
cd libraries
git clone git@github.com:jails/li3_access.git
```

Include the library in your `/app/config/bootstrap/libraries.php`

```
Libraries::add('li3_access');
```

## Presentation

This plugin provide a couple of adapters for managing access control into your application. It can manage simple rule based system as well as access control lists system. Access control lists are a way to manage application permissions in a fine-grained. It's not as fast as rule based system but allow further control on your application/models.

## API

### Simple adapter:

The simple adapter only checks that the passed data is not empty. It's more a "gadget" thas a really usefull adapter.

```php
Access::config('simple' => array('adapter' => 'Simple'));
```

### Rule adapter:

The rule adapter check access from a predefinied/custom closure.

```php
Access::config('rules' => array('adapter' => 'Rules'));
```

There's some predefinied rules : `'allowAll'`, `'denyAll'`, `'allowAnyUser'`, `'allowIp'`.

you can use it like the following:

```php
$user = Auth::check('auth_config_name');
Access::check('rules', $user, $request, array('rules' => array('allowAnyUser'));

$user = User::find('first', array('username' => 'psychic'));
Access::check('rules', $user, $request, array('rules' => array('allowAnyUser'));
```

Rule with parameters:

```php
$rules = array('allowIp' => array('ip' => '/10\.0\.1\.\d+/'));
Access::check('rules', null, $request,  array(
	'rules' => array(
		'allowIp' => array(
			'ip' => '/10\.0\.1\.\d+/' //parameter for the `'allowIp'` rule.
		)
	)
));
```

Adding a custom rule:

```php
Access::config('rules' => array(
	'adapter' => 'Rules',
	'rules' => array(
		'testDeny' => array(
			'message' => 'Access denied.',
			'allow' => function($requester) {
				return false;
			}
		)
	)
));
//or:
Access::rules('rules', 'testDeny', function($requester) { return false; }, array(
	'message' => 'Access denied.'
));
```

### DbAcl adapter:

This adapter currently works for SQL databases.

```php
Access::config('acl' => array('adapter' => 'DbAcl'));
```

Access control lists, or ACL, handle two main things: things that want stuff, and things that are wanted. This is usually represented by:

- Access Control Object (Aco), i.e. something that is wanted
- Access Request Object (Aro), i.e. Something that wants something

And beetween Acos and Aros, there's permissions which define the access privileges.

Above the MySQL database schema needed to makes things works out of the box:

```sql
DROP TABLE IF EXISTS `acos`;
CREATE TABLE `acos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `class` varchar(255) DEFAULT NULL,
  `fk_id` int(10) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
);


DROP TABLE IF EXISTS `aros`;
CREATE TABLE `aros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `class` varchar(255) DEFAULT NULL,
  `fk_id` int(10) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `lft` int(10) DEFAULT NULL,
  `rght` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
);


DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aro_id` int(10) NOT NULL,
  `aco_id` int(10) NOT NULL,
  `privileges` text,
  PRIMARY KEY (`id`)
);


DROP TABLE IF EXISTS `privileges`;
CREATE TABLE `privileges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

```

Of course you need to adapt this schema according your database.

Once Acos and Aros are correctly defined (see test's fixtures for a better understanding of what Acos and Aros looks like).

You can add privileges:

```php
Access::allow('acl', 'admin/max', 'controller/backend', array('read', 'create', 'update', 'delete'));
//or:
Access::allow('acl', 'admin/max', 'controller/backend', '*');
//or:
$user = User::find('first', array('username' => 'max'));
Access::allow('acl', $user, 'controller/backend', '*');
```

You can remove privileges:

```php
Access::deny('acl', 'user/joe', 'controller/backend', array('delete'));
```

Use `Access::check()` to check some privileges:

```php
Access::check('acl', 'user/joe', 'controller/backend', array('delete'));
```

Or `Access::get()` for recovering all privileges for an Aro/Aco:

```php
Access::get('acl', 'user/joe', 'controller/backend');
```

## Greetings

The li3 team, Tom Maiaroto, Weluse, rich97, CakePHP's ACL, Pamela Anderson and all others which make that possible.
