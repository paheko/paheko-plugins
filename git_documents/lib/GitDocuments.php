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

		$cmd = '{ cd %s && git reset --merge 2>&1 && git add -A 2>&1 && git commit -a -m %s %s 2>&1; } > /dev/null &';
		$cmd = sprintf($cmd, escapeshellarg(\Garradin\FILE_STORAGE_CONFIG), escapeshellarg(self::MESSAGE), $user_arg);

		exec($cmd, $out, $code);

		if ($code) {
			throw new \RuntimeException(sprintf('Git command (%s) failed: %s', $cmd, implode("\n", $out)));
		}
	}
}