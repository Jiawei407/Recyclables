# APU account identity requirements

- Registered users: email must end with the exact domain @mail.apu.edu.my; Student ID must be uppercase TP followed by six digits, such as TP090526.
- Administrator, coordinator and faculty accounts: a normal valid email address is accepted. Administrator/coordinator examples use @mail.apu.edu.my. Staff/university IDs may retain their existing format.
- Every newly created or changed password: 8-72 characters and at least one special character, such as @, ! or #. Passwords are stored as hashes, never readable plaintext.
- The same server checks apply during registration, profile updates and administrator account editing. Changing a password uses the shared password validator. Optional empty password fields when editing an account keep the existing hash.
- MySQL also checks the student domain and TP format. Special-character checks happen before PHP hashes the password; a database cannot inspect the original characters of a password hash.

The account redraft retains all existing user IDs, names, roles, faculties, statuses and permissions. Challenges, evidence, reports, points, achievements and audit records remain attached to the same IDs. Test credentials are listed in TEST-ACCOUNTS.md. These shared test passwords are intended for the local assignment demonstration.

No other feature behavior or stylesheet is changed. Original account fields are backed up outside the public website folder.
