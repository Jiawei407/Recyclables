# Faculty achievement totals

Faculty Insights -> Achievements now shows collective approved student activity from the logged-in representative's assigned faculty. It no longer counts the representative's personal submissions.

The same progress function accepts an optional faculty name. Student pages omit it and retain individual progress and personal campaign awards. Faculty queries require the matching faculty, Registered User role and approved evidence status. Bottle/cup rules sum the approved quantities; activity rules count approved submission records. Every configured rule must be satisfied.

Faculty cards say "Collective student progress" and "Faculty target met" to distinguish shared totals from individually earned badges. These cards display progress; viewing them does not award badges or modify records.

No schema change or SQL import is required. The database remains in MySQL; PHP performs the correctly scoped read queries. Verification uses an isolated in-memory test fixture, not the installed MySQL database.
