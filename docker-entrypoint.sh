#!/bin/sh
# Write Docker Compose runtime env vars into a local .env file so Vite's
# loadEnv() can read them. This avoids relying on process.env inheritance
# across npx/vite child processes in containerised E2E runs.
#
# NOTE: JSON defaults must NOT be placed inside ${VAR:-...} in a heredoc: the
# first "}" of the JSON would terminate the expansion and leave a stray "'}".
set -e

: "${VITE_APP_DEV_URL:=http://api:8000/index.php}"
: "${VITE_APP_DEV_THEME_URL:=/images}"
: "${VITE_APP_DEV_SESSION_VAR:=sc}"
: "${VITE_APP_DEV_SESSION_ID:=e2e_session}"
: "${VITE_APP_DEV_USER_ID:=1}"
: "${VITE_APP_DEV_WALL_ID:=1}"
: "${VITE_APP_DEV_IS_CURRENT_USER_OWNER:=true}"
: "${VITE_APP_DEV_USE_MOOD:=false}"

[ -n "${VITE_APP_DEV_TEXT_TABS:-}" ] || VITE_APP_DEV_TEXT_TABS='{"wall":"Wall","about":"About me","activity":"Recent activity"}'
[ -n "${VITE_APP_DEV_TEXT_ERROR:-}" ] || VITE_APP_DEV_TEXT_ERROR='{"wrongValues":"Wrong values were sent","errorEmpty":"You need to type something!","noStatus":"No status to display","generic":"There was an error"}'
[ -n "${VITE_APP_DEV_TEXT_LIKE:-}" ] || VITE_APP_DEV_TEXT_LIKE='{"like":"Like","unlike":"Unlike"}'
[ -n "${VITE_APP_DEV_TEXT_ACTIONS:-}" ] || VITE_APP_DEV_TEXT_ACTIONS='{"like":"Like","comment":"Comment","delete":"Delete"}'
[ -n "${VITE_APP_DEV_TEXT:-}" ] || VITE_APP_DEV_TEXT='{"deletedStatus":"Your status has been deleted","deletedComment":"Your comment was deleted!","save":"Save","delete":"Delete","editing":"Editing","close":"Close","cancel":"Cancel","send":"Send","preview":"Preview","previewing":"Previewing","end":"No more status to display","loadMore":"Load more","goUp":"Go Up","emptyData":"No data available"}'

{
  printf 'VITE_APP_DEV_URL=%s\n' "$VITE_APP_DEV_URL"
  printf 'VITE_APP_DEV_THEME_URL=%s\n' "$VITE_APP_DEV_THEME_URL"
  printf 'VITE_APP_DEV_SESSION_VAR=%s\n' "$VITE_APP_DEV_SESSION_VAR"
  printf 'VITE_APP_DEV_SESSION_ID=%s\n' "$VITE_APP_DEV_SESSION_ID"
  printf 'VITE_APP_DEV_USER_ID=%s\n' "$VITE_APP_DEV_USER_ID"
  printf 'VITE_APP_DEV_WALL_ID=%s\n' "$VITE_APP_DEV_WALL_ID"
  printf 'VITE_APP_DEV_IS_CURRENT_USER_OWNER=%s\n' "$VITE_APP_DEV_IS_CURRENT_USER_OWNER"
  printf 'VITE_APP_DEV_USE_MOOD=%s\n' "$VITE_APP_DEV_USE_MOOD"
  printf 'VITE_APP_DEV_TEXT_TABS=%s\n' "$VITE_APP_DEV_TEXT_TABS"
  printf 'VITE_APP_DEV_TEXT_ERROR=%s\n' "$VITE_APP_DEV_TEXT_ERROR"
  printf 'VITE_APP_DEV_TEXT_LIKE=%s\n' "$VITE_APP_DEV_TEXT_LIKE"
  printf 'VITE_APP_DEV_TEXT_ACTIONS=%s\n' "$VITE_APP_DEV_TEXT_ACTIONS"
  printf 'VITE_APP_DEV_TEXT=%s\n' "$VITE_APP_DEV_TEXT"
  printf 'VITE_APP_DEV_OWNER_SETTINGS=%s\n' "${VITE_APP_DEV_OWNER_SETTINGS:-}"
} > /app/.env

# Log the generated file for CI diagnostics
cat /app/.env

exec "$@"
