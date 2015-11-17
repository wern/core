#!/usr/bin/env bash
SCRIPT=`realpath $0`
SCRIPTPATH=`dirname $SCRIPT`

cd "$SCRIPTPATH"

# start the server
php -S 127.0.0.1:8888 -t "$SCRIPTPATH/../../../.." &

if [ ! -d CalDAVTester ]; then
    svn checkout http://svn.calendarserver.org/repository/calendarserver/CalDAVTester/trunk CalDAVTester
fi
if [ ! -d pycalendar ]; then
    svn checkout http://svn.calendarserver.org/repository/calendarserver/PyCalendar/trunk/ pycalendar
fi

# create test user
cd "$SCRIPTPATH/../../../../"
OC_PASS=user01 php occ user:add --password-from-env user01
OC_PASS=user02 php occ user:add --password-from-env user02
cd "$SCRIPTPATH/../../../../"

# run the tests
cd "$SCRIPTPATH/CalDAVTester"
PYTHONPATH="$SCRIPTPATH/pycalendar/src" python testcaldav.py --print-details-onfail -s "$SCRIPTPATH/caldavtest/config/serverinfo.xml" -o cdt.txt \
	"$SCRIPTPATH/caldavtest/tests/CardDAV/current-user-principal.xml"

