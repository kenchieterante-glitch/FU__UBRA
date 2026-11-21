# FU MIS API Integration (UBRA)

Source: `app/Libraries/FuMisClient.php`, `app/Controllers/AuthController.php`, `app/Controllers/PersonnelController.php::misLookup`.

## Configuration (`.env`)
| Key | Purpose |
|---|---|
| `FU_MIS_API_URL` | `https://mis.foundationu.com/sandbox/api/v1` |
| `FU_MIS_API_KEY` | UBRA's API key (sent as `API-Key` header). The key is IP-restricted on the MIS side. |
| `database.default.database` | `fu_ubra` |

The API runs on the FU MIS server, not on the local XAMPP server. UBRA only calls it over HTTPS.

## Endpoints used (base = `FU_MIS_API_URL`)
| Method | URL | Used for |
|---|---|---|
| POST | `/v1/employees/login` | Employee sign-in. Body: `{"employee_id": "...", "password": "..."}`; header `API-Key`. Returns `access_token`, `refresh_token`, `expires_at`. |
| POST | `/tokens/generate` | UBRA app token step 1 (header `API-Key`) → `refresh_token`. |
| POST | `/tokens/refresh` | UBRA app token step 2 (headers `API-Key`, `Authorization: Bearer <refresh_token>`) → `access_token`. |
| GET | `/v1/employees` (`?search=&department=&limit=&page=`) | Employee list for Personnel Management. |
| GET | `/v1/employees/{employee_id}` | Single employee lookup. |
| GET | `/v1/employees/departments` | Department list. |

Requests that send data (login, token generate/refresh) use **POST**; lookups use **GET** with `Authorization: Bearer <access_token>`.

## Sign-in flow
1. The login form posts `employee_id` + `password` to UBRA (`POST /login`).
2. UBRA forwards them to `POST /v1/employees/login`. A 200 with an `access_token` means the FU account is valid; the token is stored in the session (`mis_access_token`).
3. The employee must also have a UBRA account (role) created by the administrator.
4. Fallback: if the account has a locally stored password (test/seed accounts) and the MIS login did not succeed, the local password hash is checked. This is the "temporary password" path in `AuthController::attemptLogin` (`$isValid = $misLogin['ok']` then `password_verify(...)`). Failed sign-ins are throttled (5 tries / 5 minutes) and every sign-in/out is written to the audit log.

## Testing with Postman
1. `POST {FU_MIS_API_URL}/employees/login` → Headers: `API-Key: <key>`, `Content-Type: application/json`; Body (raw JSON): `{"employee_id":"<id>","password":"<password>"}`. Expect `200` and an `access_token`.
2. `GET {FU_MIS_API_URL}/employees?department=<code>` with `Authorization: Bearer <access_token>` and `API-Key`.
3. Postman must run from an IP registered in the API Sandbox.
