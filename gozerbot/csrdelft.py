# csrdelft.py
#

""" interface to http://csrdelft.nl/ """

__author__ = 'Hans van Kranenburg <hans@knorrie.org>'
__copyright__ = 'this file is in the public domain'

from gozerbot.commands import cmnds
from gozerbot.examples import examples
from gozerbot.users import users
from gozerbot.generic import useragent, geturl, rlog
from gozerbot.plughelp import plughelp
from gozerbot.datadir import datadir
from gozerbot.persist import Persist

from urllib import urlencode

import simplejson, re, os, urllib2

plughelp.add('csrdelft', 'functionaliteit voor C.S.R. leden')

# bot user <-> ledenlijst uid mapping
csruidmap = Persist(os.path.join(datadir, 'csruidmap'))
if not csruidmap.data:
    csruidmap.data = {}

# connection settings
csrconfig = Persist(os.path.join(datadir, 'csr-config'))
if not csrconfig.data:
    csrconfig.data = {'proto': '', 'url': '', 'user': '', 'pass': ''}

class CsrRequest:
    """ afhandelen van communicatie met de website """

    def __init__(self, action, username):
        self.params = {'a':action}
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
        except urllib2.URLError, e:
            if hasattr(e, 'reason'):
                self.error = 'request failed, reason: %s' % e.reason
            elif hasattr(e, 'code'):
                self.error = 'request failed, error code: %s' % e.code
            return False
        try:
            self.result = simplejson.loads(resultstring)
        except (TypeError, ValueError):
            self.error = 'malformed server response'
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

def handle_deluid(bot, ievent):
    """ verwijder koppeling met de ledenlijst """
    username = users.getname(ievent.userhost)
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
        (float(request.result['soccie']), float(request.result['maalcie'])))
    else:
        ievent.reply('er is geen saldo-informatie beschikbaar')

cmnds.add('csr-saldo', handle_saldo, 'CSRDELFT')
examples.add('csr-saldo', 'saldo bij soccie en maalcie opvragen', 'csr-saldo')

def handle_maalabo(bot, ievent):
    """ actieve maaltijd-abo's opvragen """
    username = users.getname(ievent.userhost)
    request = CsrRequest('getabo', username)
    if not request.execute():
        ievent.reply(request.error)
        return
    if request.result:
        ievent.reply('actieve abonnementen: ', request.result, dot=True)
    else:
        ievent.reply('u heeft geen actieve maaltijdabonnementen')

cmnds.add('csr-maalabo', handle_maalabo, 'CSRDELFT')
examples.add('csr-maalabo', handle_maalabo.__doc__, 'csr-maalabo')

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

cmnds.add('csr-jarig', handle_jarig, 'CSRDELFT')
examples.add('csr-jarig', handle_jarig.__doc__, 'csr-jarig')

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

cmnds.add('csr-profiel', handle_profiel, 'CSRDELFT')
examples.add('csr-profiel', handle_profiel.__doc__, '1) profiel 2) profiel 9808')

def handle_zoek(bot, ievent):
    """ zoeken in de ledenlijst """
    username = users.getname(ievent.userhost)
    if ievent.cmnd == 'csr-zoek':
        request = CsrRequest('zoek', username)
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

cmnds.add('csr-zoek', handle_zoek, 'CSRDELFT')
examples.add('csr-zoek', handle_zoek.__doc__, 'csr-zoek piet')

cmnds.add('csr-zoekoud', handle_zoek, 'CSRDELFT')
examples.add('csr-zoekoud', 'zoeken in de oudledenlijst', 'csr-zoekoud piet')

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

# vim:ts=4:sw=4:expandtab
