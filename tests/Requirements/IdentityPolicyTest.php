<?php

use PHPUnit\Framework\TestCase;

/**
 * User-approved identity rules. Failures expose differences in the application;
 * do not loosen these expectations to match the older ACCOUNT-POLICY.md.
 */
final class IdentityPolicyTest extends TestCase
{
    private function validate(string $role, string $email, string $studentId = 'TP090526'): string
    {
        return account_error('Aina Rahman', $email, $studentId, 'School of Computing',
            'Campus!2026', 'Campus!2026', true, $role);
    }

    /** @dataProvider allRolesWithUniversityEmail */
    public function testUniversityDomainIsAcceptedForEveryRole(string $role): void
    {
        self::assertSame('', $this->validate($role, 'aina@mail.apu.edu.my'));
    }

    public static function allRolesWithUniversityEmail(): array
    {
        return [['user'], ['faculty'], ['coordinator'], ['admin']];
    }

    /** @dataProvider everyRoleWithWrongDomains */
    public function testEveryRoleRequiresTheExactUniversityDomain(string $role, string $email): void
    {
        self::assertNotSame('', $this->validate($role, $email),
            "$role accounts must reject $email; only @mail.apu.edu.my is allowed.");
    }

    public static function everyRoleWithWrongDomains(): array
    {
        $cases = [];
        foreach (['user', 'faculty', 'coordinator', 'admin'] as $role) {
            foreach (['aina@example.com', 'aina@sub.mail.apu.edu.my', 'aina@mail.apu.edu.my.attacker.test'] as $email) {
                $cases[$role . ': ' . $email] = [$role, $email];
            }
        }
        return $cases;
    }

    /** @dataProvider caseInsensitiveStudentIds */
    public function testStudentIdPrefixIsCaseInsensitive(string $studentId): void
    {
        self::assertSame('', $this->validate('user', 'aina@mail.apu.edu.my', $studentId),
            "Student ID $studentId must be accepted: TP prefix is case-insensitive.");
    }

    public static function caseInsensitiveStudentIds(): array
    {
        return ['uppercase' => ['TP090526'], 'lowercase' => ['tp090526'],
            'mixed first letter' => ['Tp090526'], 'mixed second letter' => ['tP090526']];
    }

    /** @dataProvider invalidStudentIds */
    public function testStudentIdRequiresExactlySixAsciiDigits(string $studentId): void
    {
        self::assertNotSame('', $this->validate('user', 'aina@mail.apu.edu.my', $studentId));
    }

    public static function invalidStudentIds(): array
    {
        return [['TP12345'], ['TP1234567'], ['TP12345A'], ['TP１２３４５６'],
            ['TP123456 extra'], ['123456'], ['ST123456']];
    }
}
