<?php

declare(strict_types=1);

namespace Webinertia\Validator;

use Laminas\Validator\AbstractValidator;
use Traversable;

use function array_shift;
use function count;
use function func_get_args;
use function is_array;
use function iterator_to_array;
use function preg_match_all;
use function strlen;

final class PasswordRequirement extends AbstractValidator
{
    /** Supported special characters */
    public const POSSIBLE_SPECIAL_CHARS = '/[].\'"+=\[\\\\@_!\#$%^&*()<>?|}{~:-]/';
    public const INVALID_LENGTH_COUNT   = 'invalidLengthCount';
    public const INVALID_UPPER_COUNT    = 'invalidUpperCount';
    public const INVALID_LOWER_COUNT    = 'invalidLowerCount';
    public const INVALID_DIGIT_COUNT    = 'invalidDigitCount';
    public const INVALID_SPECIAL_COUNT  = 'invalidSpecialCount';

    protected $messageTemplates = [
        self::INVALID_LENGTH_COUNT  => "Password must be at least %length% characters in length.",
        self::INVALID_UPPER_COUNT   => "Password must contain at least %upper% uppercase letter(s).",
        self::INVALID_LOWER_COUNT   => "Password must contain at least %lower% lowercase letter(s).",
        self::INVALID_DIGIT_COUNT   => "Password must contain at least %digit% numeric character(s).",
        self::INVALID_SPECIAL_COUNT => "Password must contain at least %special% special character(s)."
    ];

    protected $messageVariables = [
        'length'  => ['options' => 'length'],
        'upper'   => ['options' => 'upper'],
        'lower'   => ['options' => 'lower'],
        'digit'   => ['options' => 'digit'],
        'special' => ['options' => 'special'],
    ];

    protected $options = [
        'length'  => 0, // overall length of password
        'upper'   => 0, // uppercase count
        'lower'   => 0, // lowercase count
        'digit'   => 0, // digit count
        'special' => 0, // special char count
    ];

    /**
     * @param array<string, mixed>|Traversable<string, mixed> $options
     * @return void
     */
    public function __construct($options = [])
    {
        if ($options instanceof Traversable) {
            $options = iterator_to_array($options);
        } elseif (! is_array($options)) {
            $options        = func_get_args();
            $temp['length'] = array_shift($options);
            $options        = $temp;
        }
        parent::__construct($options);
    }

    public function isValid($value)
    {
        $this->setValue($value);
        $isValid = true;
        $options = $this->getOptions();

        if (isset($options['length']) && strlen($value) < (int) $options['length']) {
            $this->error(self::INVALID_LENGTH_COUNT);
            $isValid = false;
        }

        if (isset($options['upper']) && (int) $options['upper'] > 0) {
            preg_match_all('/[A-Z]/', $value, $upperMatches);
            if (! (count($upperMatches[0]) >= $options['upper'])) {
                $this->error(self::INVALID_UPPER_COUNT);
                $isValid = false;
            }
        }

        if (isset($options['lower']) && (int) $options['lower'] > 0) {
            preg_match_all('/[a-z]/', $value, $lowerMatches);
            if (! (count($lowerMatches[0]) >= $options['lower'])) {
                $this->error(self::INVALID_LOWER_COUNT);
                $isValid = false;
            }
        }

        if (isset($options['digit']) && (int) $options['digit'] > 0) {
            preg_match_all('/[0-9]/', $value, $digitMatches);
            if (! (count($digitMatches[0]) >= $options['digit'])) {
                $this->error(self::INVALID_DIGIT_COUNT);
                $isValid = false;
            }
        }

        if (isset($options['special']) && (int) $options['special'] > 0) {
            preg_match_all(self::POSSIBLE_SPECIAL_CHARS, $value, $specialMatches);
            if (! (count($specialMatches[0]) >= $options['special'])) {
                $this->error(self::INVALID_SPECIAL_COUNT);
                $isValid = false;
            }
        }

        return $isValid;
    }

    public function setValue($value)
    {
        $this->value = (string) $value;
    }
}
