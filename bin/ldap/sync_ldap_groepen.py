#!/usr/bin/env python

from settings import dbuser, dbpass, ldapuser, ldappass

dryrun = True

import sys
if '-y' in sys.argv:
    dryrun = False
else:
    print "N.B. wijzigingen worden alleen daadwerkelijk doorgevoerd als u dit script met argument -y draait"

print "Verbinden naar mysql..."
import MySQLdb
db = MySQLdb.connect(host="localhost", user=dbuser, passwd=dbpass, db="csrdelft")
cursor = db.cursor()
print "Verbinden naar ldap..."
import ldap
l = ldap.open('ldap1.mendix.net')
l.simple_bind_s(ldapuser,ldappass)

# http://www.daniel-lemire.com/blog/archives/2006/05/10/flattening-lists-in-python/
def flatten(nested):
    return reduce(lambda a, b: isinstance(b, (list, tuple)) and a+list(b) or a.append(b) or a, nested, [])

# haal de groepnamen en groepid's op uit de db
dbnamen = set()
dbuids = {}
#cursor.execute("select snaam, id from groep where status='ht' and gtype in (select id from groeptype where syncWithLDAP = 1)")
cursor.execute("select distinct snaam from groep where gtype in (select id from groeptype where syncWithLDAP = 1)")
result = cursor.fetchall() # ('Cie',1)
for entry in result:
    #cursor.execute("select uid from groeplid where groepid=%d" % entry[1])
    cursor.execute("select distinct uid from groeplid where groepid=(select id from groep where status='ft' and snaam='%s')  or groepid=(select id from groep where status='ht' and snaam='%s')  or groepid=(select id from groep where status='ot' and snaam='%s' order by begin desc limit 1)" % (entry[0],entry[0],entry[0]))
    naam = entry[0]
    uids = flatten(cursor.fetchall())
    dbnamen.add(naam)
    dbuids[naam] = set(uids)
#print "Groepen uit db met status 'ht' en groeptype commissie:", dbnamen
print len(dbnamen), "groepen in db"

# In [57]: l.search_s(base='ou=groepen,dc=csrdelft,dc=nl', scope=ldap.SCOPE_ONELEVEL, filterstr='cn=*')
# Out[57]: 
# [('cn=Test,ou=groepen,dc=csrdelft,dc=nl',
#   {'cn': ['Test'],
#   'member': ['uid=9808,ou=leden,dc=csrdelft,dc=nl',
#               'uid=0431,ou=leden,dc=csrdelft,dc=nl',
#               'uid=0436,ou=leden,dc=csrdelft,dc=nl',
#               'uid=0622,ou=leden,dc=csrdelft,dc=nl'],
#    'objectClass': ['top', 'groupOfNames']})]
ldapnamen = set()
ldapuids = {}
for entry in l.search_s(base='ou=groepen,dc=csrdelft,dc=nl', scope=ldap.SCOPE_ONELEVEL, filterstr='cn=*'):
    naam = entry[1]['cn'][0]
    uids = [x[4:8] for x in entry[1]['member']]
    ldapnamen.add(naam)
    ldapuids[naam] = set(uids)
#print "Groepen uit ldap:", ldapnamen
print len(ldapnamen), "groepen in ldap"

# welke groepen staan wel in de db maar niet in ldap?
missing = dbnamen - ldapnamen
for naam in missing:
    new = [('objectClass', ['top', 'groupOfNames']),
           ('cn', [naam]),
           ('member', ["uid=%s,ou=leden,dc=csrdelft,dc=nl" % x for x in dbuids[naam]]),
          ]
    print "Missende groep in ldap:", naam
    if not dryrun:
        l.add_s('cn=%s,ou=groepen,dc=csrdelft,dc=nl' % naam, new)

# welke groep staat wel in ldap maar niet in de db?
toremove = ldapnamen - dbnamen
for naam in toremove:
    print "Groep in ldap die niet (meer) in de db staat:", naam
    if not dryrun:
        l.delete('cn=%s,ou=groepen,dc=csrdelft,dc=nl' % naam)

present = ldapnamen & dbnamen
#print "Groepen die zowel in db als in ldap staan:", present
for naam in present:
    modify = []
    for uid in dbuids[naam] - ldapuids[naam]:
        modify.append((ldap.MOD_ADD, 'member', "uid=%s,ou=leden,dc=csrdelft,dc=nl" % uid))
    for uid in ldapuids[naam] - dbuids[naam]:
        modify.append((ldap.MOD_DELETE, 'member', "uid=%s,ou=leden,dc=csrdelft,dc=nl" % uid))
    if modify:
        print "Wijzigingen (erbij=%d, weg=%d) in groep %s" % (ldap.MOD_ADD, ldap.MOD_DELETE, naam), modify 
        if not dryrun:
            l.modify_s('cn=%s,ou=groepen,dc=csrdelft,dc=nl' % naam, modify)

l.unbind()
db.close()

# vim:expandtab
