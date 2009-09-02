<?php
/**
 * DooSqlMagic class file.
 *
 * @author Leng Sheng Hong <darkredz@gmail.com>
 * @link http://www.doophp.com/
 * @copyright Copyright &copy; 2009 Leng Sheng Hong
 * @license http://www.doophp.com/license
 */

/*
* Session class, manage all session options
* call it from: $session = Doo::session("mysite");
* add something to session $session->someVariable = "something";
* get that variable $var = $session=>get("someVariable"); (returns "something")
*
* @author Milos Kovacki <kovacki@gmail.com>
* @copyright &copy; 2009 Milos Kovacki
* @license http://www.doophp.com/license
*/
class DooSession {

    /*
    * Namespace - Name of Doo session namespace
    *
    * @var string
	*/

    protected $_namespace = "Default";

	/*
	* Variable that defines if session started
	*
	* @var boolean
	*/
    protected $_sessionStarted = false;

	/*
	* Variable that defines if session destroyed
	*
	* @var boolean
	*/
    protected $_sessionDestroyed = false;

	/*
	* Constructor - returns an instance object of the session that is named by namespace name
	*
	* @param string $namespace - Name of session namespace
	* @return void
	*/

    public function __construct($namespace = 'Default') {

		// should not be empty
		if ($namespace === '') {
            throw new DooSessionException('Namespace cant be empty string.');
        }
		// should not start with underscore
        if ($namespace[0] == "_") {
            throw new DooSessionException('You cannot start session with underscore.');
        }
		// should not start with numbers
        if (preg_match('#(^[0-9])#i', $namespace[0])) {
            throw new DooSessionException('Session should not start with numbers.');
        }

        $this->_namespace = $namespace;

	}

	/*
	* Start session
	*
	* @return void
	*/

	public function start() {
		// check if session is started if it is return
		if ($this->_sessionStarted) {
            return;
        }
		session_start();
		$this->_sessionStarted = true;
	}

	/*
	* Checks if session started
	*
	* @return boolean
	*/

    public static function isStarted()
    {
		if (isset($_SESSION)) {
			return true;
		} else {
			return false;
		}
    }

	/*
	* Set variable into session
	* @param string $name Name of key
	* @param mixed $value Value for keyname ($name)
	*/

	public function __set($name, $value) {
		if ($name === "")
			throw new DooSessionException("Keyname should not be empty string!");
		if (!$this->_sessionStarted)
			throw new DooSessionException("Session not started.");
		$name = (string)$name;
		$_SESSION[$this->_namespace][$name] = $value;
	}

	/*
	* Destroy all session data
	*/

	public function destroy() {
		if (!$this->_sessionStarted) {
			throw new DooSessionException("Session not started.");
			return;
		}
		if ($this->_sessionDestroyed) {
			throw new DooSessionException("Session already destroyed.");
			return;
		}
		session_destroy();
		$this->_sessionStarted = false;
		$this->_sessionDestroyed = true;
	}

	/*
	* Check wheather session is destroyed or not
	*
	* @return boolean
	*/

    public static function isDestroyed() {
        return $this->_sessionDestroyed;
    }

	/*
	*  Unset namespace or value in it
	*
	*  @param string $name If name is provided it will unset some value in session namespace
	*  if not it will unset session.
	*/

	public function namespaceUnset($name = null) {
		if (!$this-_sessionStarted) {
			throw new DooSessionException("Session not started, use DooSession::start()");
			return;
		}
		$name = (string)$name;
		if ($name === '') {
			unset($_SESSION[$this->_namespace]);
		} else {
			unset($_SESSION[$this->_namespace][$name]);
		}
	}

	/*
	* Get session id
	*/

	public static function getId() {
		if (!$this-_sessionStarted) {
			throw new DooSessionException("Session not started, use DooSession::start()");
			return;
		}
        return session_id();
    }

	/*
	* Sets session id
	*
	* @param $id session identifier
	*/

	public function setId($id) {
		if ($this->_sessionStarted)
			throw new DooSessionException("Session is already started, id must be set before.");
		if (!is_string($id) || $id === '')
			throw new DooSessionException("Session id must be string and cannot be empty.");
		if (headers_sent($filename, $linenum))
			throw new DooSessionException("Headers already sent, output started at " . $filename . " on line " . $linenum);
		session_id($id);
	}

	/*
	* Get all variables from namespace in a array
	*
	* @return array Variables from session
	*/

	public function getAll() {
		$sessionData  = (isset($_SESSION[$this->_namespace]) && is_array($_SESSION[$this->_namespace])) ?
            $_SESSION[$namespace] : array();
		return $sessionData;
	}

	/*
	* Get variable from namespace by reference
	*
	* @param string $name if that variable doesnt exist returns null
	*
	* @return mixed
	*/

	public function &get($name) {
		if (!$this-_sessionStarted) {
			throw new DooSessionException("Session not started, use DooSession::start()");
			return;
		}
		$name = (string)$name;
		if ($name === '')
			throw new DooSessionException("Name should not be empty");
		if (!isset($_SESSION[$this->_namespace][$name]))
			return null;
		else return $_SESSION[$this->_namespace][$name];
	}

	/*
	* Get value from current namespace
	*
	* @param string $name Name of variable
	*/

	public function & __get($name) {
		if (!$this-_sessionStarted) {
			throw new DooSessionException("Session not started, use DooSession::start()");
			return;
		}
		$name = (string)$name;
		return $this->get($name);
	}

}
//
class DooSessionException extends Exception {

}
?>