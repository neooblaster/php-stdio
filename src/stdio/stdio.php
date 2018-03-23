<?php
/**
 * File :: stdio.php
 *
 * A PHP Class to communicate with stdin, stdou and stderr for Linux.
 *
 * @author    Nicolas DUPRE
 * @release   23/03/2018
 * @version   0.1.0
 * @package   stdio
 *
 *
 *
 * Version 0.1.0 : 23/03/2018 : NDU
 * -------------------------------
 *
 *
 */

namespace stdio;


class stdio
{
    /**
     * 88/256 Colors :
     *
     * Some terminals can support 88 or 256 colors. Here are the control sequences that permit you to use them.
     * NOTE: The colors number 256 is only supported by vte (GNOME Terminal, XFCE4 Terminal, Nautilus Terminal,…).
     * NOTE: The 88-colors terminals (like rxvt) does not have the same color map that the 256-colors terminals.
     * For showing the 88-colors terminals color map, run the “256-colors.sh” script in a 88-colors terminal.
     *
     * Source : https://misc.flogisoft.com/bash/tip_colors_and_formatting#colors1
     */
    const COLOR_ERR = '196';
    const COLOR_INP = '220';
    const COLOR_SUC = '76';
    const COLOR_WAR = '208';
    const COLOR_TXT = '221';
    const COLOR_KWD = '39';
    const COLOR_INF = '221';
    const COLOR_NOT = '221';



    /**
     * @var array $color  Colors number defined for according to the differents kinds.
     */
    protected $colors = [];

    /**
     * @var array $kindColors  List of different kind of color registred for stdio.
     */
    protected $kindColors = ["err", "inp", "suc", "war", "txt", "kwd", "inf", "not"];

    /**
     * @var bool|resource $psdtout Pointeur vers la ressource de sortie standard.
     */
    protected $psdtout = STDOUT;

    /**
     * @var bool|resource $pstderr Pointeur vers la ressource de sortie des erreurs.
     */
    protected $pstderr = STDERR;

    /**
     * @var bool $noDie Flag pour ne pas jouer les evenements die.
     */
    protected $noDie = false;



    /**
     * stdio constructor.
     */
    public function __construct ()
    {
        // Use constant color as default. Using $kindColor to generated colors
        foreach ($this->kindColors as $idx => $kind) {
            $this->colors[$kind] = constant("self::COLOR_" . strtoupper($kind));
        }
    }

    /**
     * stdio destructor.
     */
    public function __destruct ()
    {

    }



    /**
     * Emet des messages dans le flux classique STDOUT
     *
     * @param string $message Message à afficher dans le STDOUT
     * @param array  $arg     Elements à introduire dans le message
     */
    public function stdout($message, $args = [])
    {
        $message = $this->highlight($message);
        $message = "[ INFO ] :: $message".PHP_EOL;
        fwrite($this->psdtout, vsprintf($message, $args));
    }

    /**
     * Emet des messages dans le flux STDERR de niveau WARNING ou ERROR
     *
     * @param string $message Message à afficher dans le STDERR
     * @param array  $args    Elements à introduire dans le message
     * @param int    $level   Niveau d'alerte : 0 = warning, 1 = error
     *
     * @return void
     */
    public function stderr($message, array $args = [], $level = 1)
    {
        $color_err = $this->colors['err'];
        $color_war = $this->colors['war'];

        // Traitement en fonction du niveau d'erreur
        $level_str = ($level) ? "ERROR" : "WARNING";
        $color = ($level) ? $color_err : $color_war;

        // Mise en evidence des saisie utilisateur
        $message = $this->highlight($message);
        $message = "[ \e[38;5;{$color}m$level_str\e[0m ] :: $message" . PHP_EOL;

        fwrite($this->pstderr, vsprintf($message, $args));
        if ($level && !$this->noDie) die($level);
    }

    /**
     * Met en évidence les valeurs utilisateur dans les messages
     *
     * @param  string $message Message à analyser
     *
     * @return string $message Message traité
     */
    protected function highlight($message)
    {
        $color_inp = $this->colors["inp"];

        // A tous ceux qui n'ont pas de couleur spécifiée, alors saisir la couleur par défaut
        $message = preg_replace("/(?<!>)(%[a-zA-Z0-9])/", "$color_inp>$1", $message);

        // Remplacer par le code de colorisation Shell
        $message = preg_replace("#([0-9]+)>(%[a-zA-Z0-9])#", "\e[38;5;$1m$2\e[0m", $message);

        return $message;
    }

    /**
     * Définie la ressource de sortie standard.
     *
     * @param bool|resource $stdout Pointeur vers une ressource ayant un accès en écriture.
     */
    public function setStdout($stdout = STDOUT)
    {
        $this->psdtout = $stdout;
    }

    /**
     * Définie la ressource de sortie des erreurs.
     *
     * @param bool|resource $stderr Pointeur vers une ressource ayant un accès en écriture.
     */
    public function setStderr($stderr = STDERR)
    {
        $this->pstderr = $stderr;
    }

    /**
     * Définie le comportement des fonctions die.
     * Conçue pour les tests unitaire sous phpunit.
     *
     * @param bool $nodie
     */
    public function setNoDie($nodie = false)
    {
        $this->noDie = $nodie;
    }

}
