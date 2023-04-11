#!/bin/bash
# Sync script for Kinsta production server

SSH="eastrail-staging"
# SSH="eastrail"
REMOTE_ROOT="/www/eastrail_237/public"

printf "\nWhich direction would you like to sync?\n"
DIRECTIONS="Down Up"
select direction in $DIRECTIONS; do
    printf "\nWhat would you like to sync?\n"
    TARGETS="Everything Themes Plugins Uploads Database"
    select target in $TARGETS; do
        printf "\nSyncing $target $direction\n"

        if [[ $direction == "Down" ]] && [[ $target == "Everything" ]]; then
            rsync -avz --exclude node_modules $SSH:$REMOTE_ROOT/wp-content/ ./wp-content/

            ssh $SSH "cd $REMOTE_ROOT && wp db export wp-content/mysql.sql"
            scp $SSH:$REMOTE_ROOT/wp-content/mysql.sql ./wp-content/mysql.sql
            wp db import ./wp-content/mysql.sql
        fi

        if [[ $direction == "Down" ]] && [[ $target == "Themes" ]]; then
            rsync -avz --exclude node_modules $SSH:$REMOTE_ROOT/wp-content/themes/ ./wp-content/themes/
        fi

        if [[ $direction == "Down" ]] && [[ $target == "Plugins" ]]; then
            rsync -avz --exclude node_modules $SSH:$REMOTE_ROOT/wp-content/plugins/ ./wp-content/plugins/
        fi

        if [[ $direction == "Down" ]] && [[ $target == "Uploads" ]]; then
            rsync -avz --exclude node_modules $SSH:$REMOTE_ROOT/wp-content/uploads/ ./wp-content/uploads/
        fi

        if [[ $direction == "Down" ]] && [[ $target == "Database" ]]; then
            ssh $SSH "cd $REMOTE_ROOT && wp db export wp-content/mysql.sql"
            scp $SSH:$REMOTE_ROOT/wp-content/mysql.sql ./wp-content/mysql.sql
            wp db import ./wp-content/mysql.sql
        fi

        if [[ $direction == "Up" ]] && [[ $target == "Everything" ]]; then
            rsync -avz --exclude node_modules ./wp-content/ $SSH:$REMOTE_ROOT/wp-content/

            wp db export ./wp-content/mysql.sql
            scp ./wp-content/mysql.sql $SSH:$REMOTE_ROOT/wp-content/mysql.sql
            ssh $SSH "cd $REMOTE_ROOT && wp db import wp-content/mysql.sql"
        fi

        if [[ $direction == "Up" ]] && [[ $target == "Themes" ]]; then
            rsync -avz --exclude node_modules ./wp-content/themes/ $SSH:$REMOTE_ROOT/wp-content/themes/
        fi

        if [[ $direction == "Up" ]] && [[ $target == "Plugins" ]]; then
            rsync -avz --exclude node_modules ./wp-content/plugins/ $SSH:$REMOTE_ROOT/wp-content/plugins/
        fi

        if [[ $direction == "Up" ]] && [[ $target == "Uploads" ]]; then
            rsync -avz --exclude node_modules ./wp-content/uploads/ $SSH:$REMOTE_ROOT/wp-content/uploads/
        fi

        if [[ $direction == "Up" ]] && [[ $target == "Database" ]]; then
            wp db export ./wp-content/mysql.sql
            scp ./wp-content/mysql.sql $SSH:$REMOTE_ROOT/wp-content/mysql.sql
            ssh $SSH "cd $REMOTE_ROOT && wp db import wp-content/mysql.sql"
        fi

        # exit
    done
done
