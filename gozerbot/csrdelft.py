# csrdelft.py
#
# $Id$

""" interface to http://csrdelft.nl/ """

__author__ = 'Hans van Kranenburg <hans@knorrie.org>'
__copyright__ = 'this file is in the public domain'

from gozerbot.commands import cmnds
from gozerbot.examples import examples
from gozerbot.users import users
from gozerbot.generic import useragent, geturl, rlog, waitforuser
from gozerbot.plughelp import plughelp
from gozerbot.datadir import datadir
from gozerbot.persist import Persist

from urllib import urlencode

import simplejson, re, os, urllib2, string

plughelp.add('csrdelft', 'functionaliteit voor C.S.R.-leden')

# bot user <-> ledenlijst uid mapping
csruidmap = Persist(os.path.join(datadir, 'csruidmap'))
if not csruidmap.data:
    csruidmap.data = {}

# connection settings
csrconfig = Persist(os.path.join(datadir, 'csr-config'))
if not csrconfig.data:
    csrconfig.data = {'proto': '', 'url': '', 'user': '', 'pass': ''}

def size():
    """ aantal koppelingen csr<->bot users """
    return len(csruidmap.data)

class CsrRequest:
    """ afhandelen van communicatie met de website """

    def __init__(self, function, username):
        self.params = {'fn':function}
        try:
            self.params['uid'] = csruidmap.data[username]
        except KeyError:
            pass

    def setparams(self, params):
        """ extra parameters (dict) voor in het request instellen """
        self.params.update(params)

    def execute(self):
        """ execute the request """
        if not self.params.has_key('uid'):
            self.error = 'geen koppeling met ledenlogin aanwezig, gebruik csr-setuid'
            return False
        if not csrconfig.data.has_key('url') or csrconfig.data['url'] == '':
            self.error = 'configuratiefout: de webservice-url mist'
            return False
        paramstxt = urlencode(self.params)
        url = '%s://%s?%s' % (csrconfig.data['proto'], csrconfig.data['url'], paramstxt)
        # create a http request
        request = urllib2.Request(url)
        request.add_header('User-Agent', useragent())
        # authentication stuff
        passman = urllib2.HTTPPasswordMgrWithDefaultRealm()
        passman.add_password(None, url, csrconfig.data['user'], csrconfig.data['pass'])
        authhandler = urllib2.HTTPBasicAuthHandler(passman)
        opener = urllib2.build_opener(authhandler)
        try:
            result = opener.open(request)
            resultstring = result.read()
            self.info = result.info()
            result.close()
            try:
                self.result = simplejson.loads(resultstring)
            except (TypeError, ValueError):
                if resultstring:
                    self.error = string.replace(resultstring, "\n", "") # replace newlines by nothing
                else:
                    self.error = 'malformed server response'
                return False
        except urllib2.URLError, e:
            if hasattr(e, 'reason'):
                self.error = 'request failed, reason: %s' % e.reason
            elif hasattr(e, 'code'):
                self.error = 'request failed, error code: %s' % e.code
            return False
        return True

def handle_seturl(bot, ievent):
    """ instellen webservice-url """
    if not ievent.rest:
        ievent.missing('<url>')
        return
    # (proto)://(user):(pass)@(url)
    r = re.compile(r'(?P<proto>[a-z]+)://(?:(?P<user>[^\s\/]+):(?P<pass>[^\s\/]+)@)' + \
    '?(?P<url>[^\s]+)', re.I | re.L | re.U)
    # >>> r.match('http://feut:mekker@csrdelft.nl/bot/cmnd.php').groupdict()
    # {'url': 'csrdelft.nl/bot/cmnd.php', 'proto': 'http', 'user': 'feut', 'pass': 'mekker'}
    m = r.match(ievent.rest)
    if not m:
        ievent.reply('ongeldige url opgegeven, zie help csr-seturl')
        return
    csrconfig.data = m.groupdict()
    csrconfig.save()
    ievent.reply('ok')
    
cmnds.add('csr-seturl', handle_seturl, 'OPER')
examples.add('csr-seturl', handle_seturl.__doc__, \
'csr-seturl http://feut:mekker@csrdelft.nl/bot/cmnd.php')

def handle_setuid(bot, ievent):
    """ leg koppeling met de ledenlijst
    csrrequest: getuserhosts
        params:
          '----> uid: gewenste ledenlijst-id om te koppelen
        result:
          |----> naam: civitasnaam van de gekoppelde ledenlijstuid
          '----> userhosts: [lijst van userhosts]
    """
    if not ievent.rest:
        ievent.missing('<uid>')
        return
    username = users.getname(ievent.userhost)
    uid = ievent.rest
    request = CsrRequest('getuserhosts', username)
    request.setparams({'uid': uid})
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        for userhost in request.result['userhosts']:
            if username == users.getname(userhost):
                csruidmap.data[username] = uid
                csruidmap.save()
                ievent.reply('uid %s (%s) gekoppeld' % (uid, request.result['naam']))
                return
    ievent.reply('uw huidige msn/icq/jabber/irc userhost is niet in het profiel gevonden') 

cmnds.add('csr-setuid', handle_setuid, 'CSRDELFT')
examples.add('csr-setuid', 'maak een koppeling met de ledenlijst', 'csr-setuid 9808')

def handle_getuserhosts(bot, ievent):
    """ opvragen userhosts uit C.S.R. profiel """
    username = users.getname(ievent.userhost)
    request = CsrRequest('getuserhosts', username)
    if ievent.rest:
        request.setparams({'getuid': ievent.rest})
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        if len(request.result['userhosts']) > 0:
            ievent.reply('userhosts in C.S.R. profiel: ', request.result['userhosts'], dot=True)
        else:
            ievent.reply('geen userhosts (msn,icq,jabber etc) gevonden in C.S.R. profiel')

cmnds.add('csr-getuserhosts', handle_getuserhosts, 'CSRDELFT')
examples.add('csr-getuserhosts', handle_getuserhosts.__doc__, 'csr-getuserhosts')

def handle_deluid(bot, ievent):
    """ verwijder koppeling met de ledenlijst """
    username = users.getname(ievent.userhost)
    ievent.reply('koppeling met de ledenlijst verwijderen (ja/nee)?')
    response = waitforuser(bot, ievent.userhost)
    if not response or response.txt != 'ja':
        ievent.reply('koppeling wordt niet gewist')
    else:
        try:
            del csruidmap.data[username]
        except KeyError:
            ievent.reply('geen koppeling met de ledenlijst aanwezig')
        else:
            csruidmap.save()
            ievent.reply('koppeling met de ledenlijst gewist')

cmnds.add('csr-deluid', handle_deluid, 'CSRDELFT')
examples.add('csr-deluid', handle_deluid.__doc__, 'csr-deluid')

def handle_saldo(bot, ievent):
    """ saldo bij soccie en maalcie opvragen """
    username = users.getname(ievent.userhost)
    request = CsrRequest('getsaldo', username)
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        ievent.reply('soccie: %01.2f maalcie: %01.2f' % \
        (float(request.result['soccieSaldo']), float(request.result['maalcieSaldo'])))
    else:
        ievent.reply('er is geen saldo-informatie beschikbaar')

cmnds.add('saldo', handle_saldo, 'CSRDELFT')
examples.add('saldo', 'saldo bij soccie en maalcie opvragen', 'saldo')

def handle_abolijst(bot, ievent):
    """ actieve maaltijd-abo's opvragen """
    username = users.getname(ievent.userhost)
    request = CsrRequest('abolijst', username)
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        ievent.reply('actieve abonnementen: ', request.result, dot=True)
    else:
        ievent.reply('u heeft geen actieve maaltijdabonnementen')

cmnds.add('abo-lijst', handle_abolijst, 'CSRDELFT')
examples.add('abo-lijst', handle_abolijst.__doc__, 'abo-lijst')

def handle_aboaan(bot, ievent):
    """ aanzetten van een maaltijd-abonnement """
    username = users.getname(ievent.userhost)
    if not ievent.rest:
        request = CsrRequest('getnotabos', username)
        if not request.execute():
            ievent.reply(request.error)
            return
        else:
            # N.B.: abosoorten zijn hier in kleine letters, zonder A_ ervoor, dus
            # bijv. uber1 ipv A_UBER1
            ievent.reply('beschikbare abonnementen om aan te zetten: ', request.result, dot=True)
    else:
        request = CsrRequest('addabo', username)
        request.setparams({'abosoort': ievent.rest})
        if not request.execute():
            ievent.reply(request.error)
            return
        else:
            ievent.reply(request.result)

cmnds.add('abo-aan', handle_aboaan, 'CSRDELFT')
examples.add('abo-aan', handle_aboaan.__doc__, '1) abo-aan 2) abo-aan moot2')

def handle_abouit(bot, ievent):
    """ uitzetten van een maaltijd-abonnement """
    username = users.getname(ievent.userhost)
    if not ievent.rest:
        request = CsrRequest('getwelabos', username)
        if not request.execute():
            ievent.reply(request.error)
            return
        else:
            # N.B.: abosoorten zijn hier in kleine letters, zonder A_ ervoor, dus
            # bijv. uber1 ipv A_UBER1
            ievent.reply('beschikbare abonnementen om uit te zetten: ', request.result, dot=True)
    else:
        request = CsrRequest('delabo', username)
        request.setparams({'abosoort': ievent.rest})
        if not request.execute():
            ievent.reply(request.error)
            return
        else:
            ievent.reply(request.result)

cmnds.add('abo-uit', handle_abouit, 'CSRDELFT')
examples.add('abo-uit', handle_abouit.__doc__, '1) abo-uit 2) abo-uit donderdag')

def handle_maallijst(bot, ievent):
    """ lijst met komende maaltijden opvragen """
    username = users.getname(ievent.userhost)
    request = CsrRequest('maallijst', username)
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        ievent.reply('', request.result, dot=True)
    else:
        ievent.reply('er zijn geen maaltijden binnenkort')

cmnds.add('maal-lijst', handle_maallijst, 'CSRDELFT')
examples.add('maal-lijst', handle_maallijst.__doc__, 'maal-lijst')

def handle_maalinfo(bot, ievent):
    """ uitgebreide informatie over een maaltijd, zie ook csr-maallijst """
    username = users.getname(ievent.userhost)
    request = CsrRequest('maalinfo', username)
    if not ievent.rest:
        maalid = 0
    else:
        maalid = ievent.rest
    request.setparams({'maalid': maalid})
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        ievent.reply('', request.result, dot=True)
    else:
        ievent.reply('er is een fout opgetreden in de communicatie met de website')

cmnds.add('maal-info', handle_maalinfo, 'CSRDELFT')
examples.add('maal-info', handle_maalinfo.__doc__, '1) maal-info 2) maal-info 123')

def handle_maalaan(bot, ievent):
    """ aanmelden voor een maaltijd, csr-maalaan [<maalid> [<uid>]] zie ook csr-maallijst """
    username = users.getname(ievent.userhost)
    request = CsrRequest('maalaan', username)
    try:
        maalid, proxyuid = ievent.rest.split(' ', 1)
        request.setparams({'maalid': maalid, 'proxyuid': proxyuid})
    except ValueError:
        if not ievent.rest:
            maalid = 0
        else:
            maalid = ievent.rest
    request.setparams({'maalid': maalid})
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        ievent.reply(request.result)
    else:
        ievent.reply('er is een fout opgetreden in de communicatie met de website')

cmnds.add('maal-aan', handle_maalaan, 'CSRDELFT')
examples.add('maal-aan', handle_maalaan.__doc__, '1) maal-aan 2) maal-aan 123 3) maal-aan 123 9808')

def handle_maalaf(bot, ievent):
    """ afmelden voor een maaltijd, csr-maalaf [<maalid>], zie ook csr-maallijst """
    username = users.getname(ievent.userhost)
    request = CsrRequest('maalaf', username)
    if not ievent.rest:
        maalid = 0
    else:
        maalid = ievent.rest
    request.setparams({'maalid': maalid})
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        ievent.reply(request.result)
    else:
        ievent.reply('er is een fout opgetreden in de communicatie met de website')

cmnds.add('maal-af', handle_maalaf, 'CSRDELFT')
examples.add('maal-af', handle_maalaf.__doc__, '1) maal-af 2) maal-af 123')

def handle_jarig(bot, ievent):
    """ komende 10 verjaardagen opvragen """
    username = users.getname(ievent.userhost)
    request = CsrRequest('getjarig', username)
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        ievent.reply(request.result, dot=True)
    else:
        ievent.reply('geen verjaardagen')

cmnds.add('verjaardagen', handle_jarig, 'CSRDELFT')
examples.add('verjaardagen', handle_jarig.__doc__, 'verjaardagen')

def handle_profiel(bot, ievent):
    """ profiel opvragen """
    username = users.getname(ievent.userhost)
    request = CsrRequest('getprofiel', username)
    if ievent.rest:
        request.setparams({'getuid': ievent.rest})
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        ievent.reply(request.result, dot=True)
    else:
        ievent.reply('er is geen profiel-informatie beschikbaar')

cmnds.add('profiel', handle_profiel, 'CSRDELFT')
examples.add('profiel', handle_profiel.__doc__, '1) profiel 2) profiel 9808')

def handle_zoek(bot, ievent):
    """ zoeken in de ledenlijst """
    username = users.getname(ievent.userhost)
    if ievent.command == 'zoek':
        request = CsrRequest('zoeklid', username)
    else:
        request = CsrRequest('zoekoud', username)
    if not ievent.rest:
        ievent.missing('<zoekterm>')
        return
    request.setparams({'zoekterm': ievent.rest.strip()})
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        ievent.reply(request.result, dot=True)
    else:
        ievent.reply('geen zoekresultaten')

cmnds.add('zoek', handle_zoek, 'CSRDELFT')
examples.add('zoek', handle_zoek.__doc__, 'zoek piet')

cmnds.add('zoek-oud', handle_zoek, 'CSRDELFT')
examples.add('zoek-oud', 'zoeken in de oudledenlijst', 'zoek-oud piet')

def handle_whoami(bot, ievent):
    """ wie ben ik? """
    username = users.getname(ievent.userhost)
    request = CsrRequest('whoami', username)
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        ievent.reply(request.result, dot=True)
    else:
        ievent.reply('niet bekend')

cmnds.add('csr-whoami', handle_whoami, 'CSRDELFT')
examples.add('csr-whoami', handle_whoami.__doc__, 'csr-whoami')

def handle_perms(bot, ievent):
    """ opvragen permissies """
    username = users.getname(ievent.userhost)
    request = CsrRequest('perms', username)
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        ievent.reply(request.result)
    else:
        ievent.reply('niet bekend')

cmnds.add('csr-perms', handle_perms, 'CSRDELFT')
examples.add('csr-perms', handle_perms.__doc__, 'csr-perms')

def handle_aaidrom(bot, ievent):
    """ beginletters naam omdraaien """
    username = users.getname(ievent.userhost)
    request = CsrRequest('aaidrom', username)
    if ievent.rest:
        request.setparams({'getuid': ievent.rest})
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        ievent.reply(request.result)
    else:
        ievent.reply('niet bekend')

cmnds.add('aaidrom', handle_aaidrom, 'CSRDELFT')
examples.add('aaidrom', handle_aaidrom.__doc__, 'aaidrom x101')

# vim:ts=4:sw=4:expandtab
