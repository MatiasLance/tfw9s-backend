# Player Card duplicate detection

The registration form performs a non-blocking Player Card lookup both when a
registrant chooses **I have a Player Card** and before a new Player Card
purchase. It intentionally warns rather than rejects because names are not
unique and different players can legitimately share the same name.

## Search strategy

1. Normalize case and whitespace and transliterate accented characters for
   scoring.
2. Use indexed `prefix%` queries for exact and autocomplete candidates.
3. Fetch a bounded pool of same-initial candidates and rank that small pool with
   Levenshtein similarity for minor spelling variations.
4. Exclude soft-deleted (inactive) cards and collapse repeated display rows.
5. Return at most ten results. Only the player name, masked phone suffix, team,
   age group, match type, and score are exposed.

The candidate pool is capped at 150 records so fuzzy matching does not scan the
entire players table. Exact and close matches are ranked ahead of possible
matches. The API is also limited to 30 requests per minute per requester.
Both `/players/player-card/matches` and the legacy `/players/name/suggest`
endpoint use the same privacy-safe result mapping. Full phone numbers, parent
names, email addresses, and dates of birth are never returned by either public
lookup endpoint.

The frontend waits 500 ms after typing stops and aborts superseded requests.
After a match is selected, the registrant re-enters current contact details and
the player's date of birth. This keeps private stored data out of the browser
while preserving the existing weekly registration and payment flow.

## Indexes

The players table already has an index on
`(player_firstname, player_lastname)`. Migration
`2026_07_27_000000_add_player_card_lookup_index.php` adds the reverse
`(player_lastname, player_firstname)` index for last-name autocomplete and
reversed-name input.

For very large datasets (millions of players), move normalized names into
dedicated generated columns or a search service that supports phonetic and
trigram indexes. MySQL full-text search is not the first choice here because
short names, prefixes, and one-character spelling errors are central to this
workflow.
