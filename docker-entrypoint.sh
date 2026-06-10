#!/bin/sh
# Write Docker Compose runtime env vars into a local .env file so Vite's
# loadEnv() can read them. This avoids relying on process.env inheritance
# across npx/vite child processes in containerised E2E runs.
set -e

cat > /app/.env <<EOF
VITE_APP_DEV_URL=${VITE_APP_DEV_URL:-http://api:8000/index.php}
VITE_APP_DEV_THEME_URL=${VITE_APP_DEV_THEME_URL:-/images}
VITE_APP_DEV_SESSION_VAR=${VITE_APP_DEV_SESSION_VAR:-sc}
VITE_APP_DEV_SESSION_ID=${VITE_APP_DEV_SESSION_ID:-e2e_session}
VITE_APP_DEV_USER_ID=${VITE_APP_DEV_USER_ID:-1}
VITE_APP_DEV_WALL_ID=${VITE_APP_DEV_WALL_ID:-1}
VITE_APP_DEV_IS_CURRENT_USER_OWNER=${VITE_APP_DEV_IS_CURRENT_USER_OWNER:-true}
VITE_APP_DEV_USE_MOOD=${VITE_APP_DEV_USE_MOOD:-false}
VITE_APP_DEV_TEXT_TABS=${VITE_APP_DEV_TEXT_TABS:-'{"wall":"Wall","about":"About me","activity":"Recent activity"}'}
VITE_APP_DEV_TEXT_ERROR=${VITE_APP_DEV_TEXT_ERROR:-'{"wrongValues":"Wrong values were sent","errorEmpty":"You need to type something!","noStatus":"No status to display","generic":"There was an error"}'}
VITE_APP_DEV_TEXT_LIKE=${VITE_APP_DEV_TEXT_LIKE:-'{"like":"Like","unlike":"Unlike"}'}
VITE_APP_DEV_TEXT_ACTIONS=${VITE_APP_DEV_TEXT_ACTIONS:-'{"like":"Like","comment":"Comment","delete":"Delete"}'}
VITE_APP_DEV_TEXT=${VITE_APP_DEV_TEXT:-'{"deletedStatus":"Your status has been deleted","deletedComment":"Your comment was deleted!","save":"Save","delete":"Delete","editing":"Editing","close":"Close","cancel":"Cancel","send":"Send","preview":"Preview","previewing":"Previewing","end":"No more status to display","loadMore":"Load more","goUp":"Go Up","emptyData":"No data available"}'}
VITE_APP_DEV_OWNER_SETTINGS=${VITE_APP_DEV_OWNER_SETTINGS:-}
EOF

# Log the generated file for CI diagnostics
cat /app/.env

exec "$@"
