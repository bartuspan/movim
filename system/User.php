<?php

/**
 * \class User
 * \brief Handles the user's login and user.
 *
 */
class User {
	private $xmppSession;

	private $username = '';
	private $password = '';

	/**
	 * Class constructor. Reloads the user's session or attempts to authenticate
	 * the user.
	 * Note that the constructor is private. This class is a singleton.
	 */
	function __construct()
	{
		if($this->isLogged()) {
            $sess = Session::start(APP_NAME);
			$this->username = $sess->get('login');
			$this->password = $sess->get('pass');

			$this->xmppSession = Jabber::getInstance($this->username);
		}
		else if(isset($_POST['login'])
				&& isset($_POST['pass'])
				&& $_POST['login'] != ''
				&& $_POST['pass'] != '') {
			$this->authenticate($_POST['login'], $_POST['pass']);
		}
	}

	/**
	 * Checks if the user has an open session.
	 */
	function isLogged()
	{
		// User is not logged in if both the session vars and the members are unset.
        $sess = Session::start(APP_NAME);
		return (($this->username != '' && $this->password != '') || $sess->get('login'));
	}

	function authenticate($login,$pass)
	{
		try{
            $data = false;
			if( !($data = Conf::getUserData($login)) ) {
                Conf::createUserConf($login, $pass);
                $data = Conf::getUserData($login);
            }

			$this->xmppSession = Jabber::getInstance($login);
			$this->xmppSession->login($login, $pass);

			// Careful guys, md5 is _not_ secure. SHA1 recommended here.
			if(sha1($pass) == $data['pass']) {
                $sess = Session::start(APP_NAME);
                $sess->set('login', $login);
                $sess->set('pass', $pass);

				$this->username = $login;
				$this->password = $pass;
			} else {
				throw new MovimException(t("Wrong password"));
			}
		}
		catch(MovimException $e){
			echo $e->getMessage();
			return $e->getMessage();
		}
	}

	function desauth()
	{
        $sess = Session::start(APP_NAME);
		$sess->remove('login');
        $sess->remove('pass');
        $sess->remove('jid');
        $sess->dispose(APP_NAME);
	}


	function getLogin()
	{
		return $this->username;
	}

	function getPass()
	{
		return $this->password;
	}

}
