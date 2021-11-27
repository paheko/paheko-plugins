<?php

namespace Garradin\Plugin\Git_Documents;

use Garradin\Membres\Session;

class GitDocuments
{
	const MESSAGE = "Modification depuis l'interface de Garradin";

	static public function sync()
	{
		$session = Session::getInstance();

		if ($session->isLogged()) {
			$user = $session->getUser();
			$user = sprintf('%s <%s>', $user->identite, $user->email);
			$user_arg = sprintf('--author=%s', escapeshellarg($user));
		}
		else {
			$user_arg = '';
		}

		$cmd = 'cd %s && git reset -q --merge && git add -A && git commit -q -a -m %s %s > /dev/null';
		$cmd = sprintf($cmd, escapeshellarg(\Garradin\DATA_ROOT), escapeshellarg(self::MESSAGE), $user_arg);

		shell_exec($cmd);
	}
}