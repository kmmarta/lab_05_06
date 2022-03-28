<?php
// W skrypcie definicji kontrolera nie trzeba dołączać problematycznego skryptu config.php,
// ponieważ będzie on użyty w miejscach, gdzie config.php zostanie już wywołany.

require_once $conf->root_path.'/lib/smarty/Smarty.class.php';
require_once $conf->root_path.'/lib/Messages.class.php';
require_once $conf->root_path.'/app/calc/CalcForm.class.php';
require_once $conf->root_path.'/app/calc/CalcResult.class.php';

/** Kontroler kalkulatora
 * @author Przemysław Kudłacik
 *
 */
class CalcCtrl {

	private $msgs;   //wiadomości dla widoku
	private $form;   //dane formularza (do obliczeń i dla widoku)
	private $result; //inne dane dla widoku
	//private $hide_intro; 

	/** 
	 * Konstruktor - inicjalizacja właściwości
	 */
	public function __construct(){
		//stworzenie potrzebnych obiektów
		$this->msgs = new Messages();
		$this->form = new CalcForm();
		$this->result = new CalcResult();
		//$this->hide_intro = false;
	}
	
	/** 
	 * Pobranie parametrów
	 */
	public function getParams(){
		$this->form->a= isset($_REQUEST ['a']) ? $_REQUEST ['a'] : null;
		$this->form->b = isset($_REQUEST ['b']) ? $_REQUEST ['b'] : null;
		$this->form->c = isset($_REQUEST ['c']) ? $_REQUEST ['c'] : null;
	}
	
	/** 
	 * Walidacja parametrów
	 * @return true jeśli brak błedów, false w przeciwnym wypadku 
	 */
	public function validate() {
		// sprawdzenie, czy parametry zostały przekazane
		if (! (isset ( $this->form->a ) && isset ( $this->form->b ) && isset ( $this->form->c ))) {
			// sytuacja wystąpi kiedy np. kontroler zostanie wywołany bezpośrednio - nie z formularza
			return false; //zakończ walidację z błędem
		} else { 
			$this->hide_intro = true; //przyszły pola formularza, więc - schowaj wstęp
		}
		
		// sprawdzenie, czy potrzebne wartości zostały przekazane
		if ($this->form->a == "") {
			$this->msgs->addError('Nie podano  kwoty');
		}
		if ($this->form->b == "") {
			$this->msgs->addError('Nie podano liczby liczby lat');
		}
		if ($this->form->c == "") {
			$this->msgs->addError('Nie podano oprocentowania');
		}
		// nie ma sensu walidować dalej gdy brak parametrów
		if (! $this->msgs->isError()) {
			
			// sprawdzenie, czy $x i $y są liczbami całkowitymi
			if (! is_numeric ( $this->form->a )) {
				$this->msgs->addError('wartość nie jest liczbą całkowitą');
			}
			
			if (! is_numeric ( $this->form->b)) {
				$this->msgs->addError(' wartość nie jest liczbą całkowitą');
			}
                        if (! is_numeric ( $this->form->c )) {
				$this->msgs->addError(' wartość nie jest liczbą całkowitą');
			}
		}
		
		return ! $this->msgs->isError();
	}
	
	/** 
	 * Pobranie wartości, walidacja, obliczenie i wyświetlenie
	 */
	public function process(){

		$this->getparams();
		
		if ($this->validate()) {
				
			//konwersja parametrów na int
			$this->form->a = intval($this->form->a);
			$this->form->b = intval($this->form->b);
			
				
		
			$this->form->c = intval($this->form->c);
            $this->msgs->addInfo('Parametry poprawne.');

            //wykonanie operacji
            $a = $this->form->a;
            $b = $this->form->b * 12;
            $c = $this->form->c/ 100;

            $result = ($a * $c) / (12 * (1 - ((12 / (12 + $c)) ** $b))); //wzor
            $this->result->result = number_format($result, 1, ',', ' '); 
            $this->msgs->addInfo('Wykonano obliczenia.');
        }
		$this->generateView();
	}
	
	
	/**
	 * Wygenerowanie widoku
	 */
	public function generateView(){
		global $conf;
		
		$smarty = new Smarty();
		$smarty->assign('conf',$conf);
		
		$smarty->assign('page_title','Kalkulator kredytowy');
		//$smarty->assign('page_description','Obiektowość. Funkcjonalność aplikacji zamknięta w metodach różnych obiektów. Pełen model MVC.');
		$smarty->assign('page_header','Oblicz rate');
				
		//$smarty->assign('hide_intro',$this->hide_intro);
		
		$smarty->assign('msgs',$this->msgs);
		$smarty->assign('form',$this->form);
		$smarty->assign('res',$this->result);
		
		$smarty->display($conf->root_path.'/app/calc/CalcView.html');
	}
}
