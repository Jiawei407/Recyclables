<?php

use PHPUnit\Framework\TestCase;

/** Tests the shared PHP validator directly without opening a database. */
final class AccountRulesTest extends TestCase
{
    private function account(array $changes = []): string
    {
        $account = array_replace([
            'name' => 'Aina Rahman',
            'email' => 'aina@mail.apu.edu.my',
            'id' => 'TP090526',
            'faculty' => 'School of Computing',
            'password' => 'Campus!2026',
            'confirm' => 'Campus!2026',
            'needs_password' => true,
            'role' => 'user',
        ], $changes);

        return account_error(
            $account['name'], $account['email'], $account['id'],
            $account['faculty'], $account['password'], $account['confirm'],
            $account['needs_password'], $account['role']
        );
    }

    /** @dataProvider validRoles */
    public function testSupportedRolesAreValid(string $role): void
    {
        self::assertTrue(valid_role($role));
        self::assertSame('', $this->account(['role' => $role]));
    }

    public static function validRoles(): array
    {
        return ['participant' => ['user'], 'faculty' => ['faculty'],
            'coordinator' => ['coordinator'], 'administrator' => ['admin']];
    }

    /** @dataProvider invalidRoles */
    public function testUnsupportedRolesAreRejected($role): void
    {
        self::assertFalse(valid_role($role));
        self::assertSame('Choose a valid account role.', $this->account(['role' => $role]));
    }

    public static function invalidRoles(): array
    {
        return ['empty' => [''], 'unknown' => ['superadmin'], 'wrong case' => ['Admin'],
            'number' => [0], 'null' => [null], 'array' => [['admin']]];
    }

    public function testFacultyChoicesPreserveTheFourSupportedFaculties(): void
    {
        self::assertSame([
            'School of Computing', 'School of Engineering',
            'School of Business', 'Campus Sustainability',
        ], faculties());
    }

    /** @dataProvider supportedFaculties */
    public function testEachSupportedFacultyIsAccepted(string $faculty): void
    {
        self::assertSame('', $this->account(['faculty' => $faculty]));
    }

    public static function supportedFaculties(): array
    {
        return [['School of Computing'], ['School of Engineering'],
            ['School of Business'], ['Campus Sustainability']];
    }

    /** @dataProvider invalidFaculties */
    public function testMissingOrUnknownFacultyIsRejected($faculty): void
    {
        self::assertSame('Choose a faculty.', $this->account(['faculty' => $faculty]));
    }

    public static function invalidFaculties(): array
    {
        return [[''], ['Choose your faculty'], ['School of Medicine'],
            ['school of computing'], [null]];
    }

    /** @dataProvider validNames */
    public function testMeaningfulNamesWithinTheLimitAreAccepted(string $name): void
    {
        self::assertSame('', $this->account(['name' => $name]));
    }

    public static function validNames(): array
    {
        return ['one character' => ['A'], 'below limit' => [str_repeat('A', 99)],
            'at limit' => [str_repeat('A', 100)], 'apostrophe and hyphen' => ["Nur O'Connor-Smith"],
            'unicode' => ['李嘉伟']];
    }

    /** @dataProvider invalidNames */
    public function testMissingOrOverlongNamesAreRejected(string $name): void
    {
        self::assertSame('Enter a full name of up to 100 characters.', $this->account(['name' => $name]));
    }

    public static function invalidNames(): array
    {
        return ['empty' => [''], 'spaces' => ['   '], 'tabs and newline' => ["\t\n"],
            'over limit' => [str_repeat('A', 101)]];
    }

    /** @dataProvider validStudentEmails */
    public function testUniversityEmailFormattingIsAccepted(string $email): void
    {
        self::assertSame('', $this->account(['email' => $email]));
    }

    public static function validStudentEmails(): array
    {
        return [['student@mail.apu.edu.my'], ['first.last+challenge@mail.apu.edu.my'],
            ['STUDENT@MAIL.APU.EDU.MY']];
    }

    /** @dataProvider invalidEmailFormats */
    public function testMalformedEmailsAreRejected(string $email): void
    {
        self::assertSame('Enter a valid email address.', $this->account(['email' => $email]));
    }

    public static function invalidEmailFormats(): array
    {
        return ['empty' => [''], 'no local part' => ['@mail.apu.edu.my'],
            'no at sign' => ['student.mail.apu.edu.my'], 'two at signs' => ['student@@mail.apu.edu.my'],
            'space in local part' => ['stu dent@mail.apu.edu.my'], 'missing domain' => ['student@'],
            'surrounding spaces' => [' student@mail.apu.edu.my '],
            'over email length' => [str_repeat('a', 240) . '@mail.apu.edu.my']];
    }

    /** @dataProvider wrongStudentDomains */
    public function testStudentDomainMustMatchExactly(string $email): void
    {
        self::assertSame('Student emails must end with @mail.apu.edu.my.', $this->account(['email' => $email]));
    }

    public static function wrongStudentDomains(): array
    {
        return [['student@example.com'], ['student@apu.edu.my'],
            ['student@sub.mail.apu.edu.my'], ['student@mail.apu.edu.my.attacker.test'],
            ['student@notmail.apu.edu.my']];
    }

    /** @dataProvider invalidUniversityIds */
    public function testMissingOrOverlongUniversityIdsAreRejected(string $id): void
    {
        self::assertSame('Enter a university ID of up to 20 characters.', $this->account(['id' => $id]));
    }

    public static function invalidUniversityIds(): array
    {
        return [[''], ['   '], [str_repeat('A', 21)]];
    }

    /** @dataProvider malformedStudentIds */
    public function testMalformedStudentIdsAreRejected(string $id): void
    {
        self::assertSame('Student IDs must be TP followed by six digits, for example TP090526.', $this->account(['id' => $id]));
    }

    public static function malformedStudentIds(): array
    {
        return ['short' => ['TP12345'], 'long' => ['TP1234567'], 'non-digit' => ['TP12345A'],
            'missing prefix' => ['123456'], 'wrong prefix' => ['ST123456'],
            'leading space' => [' TP123456'], 'trailing newline' => ["TP123456\n"],
            'unicode digits' => ['TP１２３４５６']];
    }

    /** @dataProvider validPasswords */
    public function testValidPasswordsPassBothPasswordAndAccountValidation(string $password): void
    {
        self::assertSame('', new_password_error($password));
        self::assertSame('', $this->account(['password' => $password, 'confirm' => $password]));
    }

    public static function validPasswords(): array
    {
        return ['minimum' => ['aaaaaaa!'], 'below maximum' => [str_repeat('a', 70) . '!'],
            'maximum' => [str_repeat('a', 71) . '!'], 'mixed characters' => ['Campus!2026'],
            'spaces retained with special character' => [' aaaaa! '],
            'unicode punctuation' => ['abcdefg★']];
    }

    /** @dataProvider invalidPasswords */
    public function testInvalidPasswordsReturnTheSharedValidationError(string $password, string $message): void
    {
        self::assertSame($message, new_password_error($password));
        self::assertSame($message, $this->account(['password' => $password, 'confirm' => $password]));
    }

    public static function invalidPasswords(): array
    {
        $length = 'Use a password between 8 and 72 characters.';
        $special = 'Include at least one special character in the password, for example @, ! or #.';
        return ['empty' => ['', $length], 'below minimum' => ['aaaaaa!', $length],
            'over maximum' => [str_repeat('a', 72) . '!', $length],
            'letters only' => ['abcdefgh', $special], 'digits only' => ['12345678', $special],
            'whitespace not special' => ['abc defg', $special],
            'unicode letters not special' => ['abcdefé', $special]];
    }

    public function testOptionalBlankEditPasswordIsAccepted(): void
    {
        self::assertSame('', $this->account(['needs_password' => false, 'password' => '', 'confirm' => '']));
    }

    public function testOptionalPasswordStillValidatesWhenProvided(): void
    {
        self::assertSame('Use a password between 8 and 72 characters.', $this->account([
            'needs_password' => false, 'password' => 'short!', 'confirm' => 'short!',
        ]));
    }

    /** @dataProvider mismatchedPasswords */
    public function testConfirmationMustMatchExactly(bool $required, string $password, string $confirm): void
    {
        self::assertSame('The passwords do not match.', $this->account([
            'needs_password' => $required, 'password' => $password, 'confirm' => $confirm,
        ]));
    }

    public static function mismatchedPasswords(): array
    {
        return ['registration mismatch' => [true, 'Campus!2026', 'Different!2026'],
            'registration missing confirmation' => [true, 'Campus!2026', ''],
            'edit mismatch' => [false, 'Campus!2026', 'Different!2026'],
            'confirmation only' => [false, '', 'Campus!2026'],
            'whitespace is significant' => [false, ' Campus!2026', 'Campus!2026']];
    }
}
