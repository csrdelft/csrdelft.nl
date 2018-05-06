                    <form method="post" action="/tools/interesse.php">
                        <div class="field">
                            <label for="name">Naam</label>
                            <input type="text" name="naam" id="naam" required/>
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input type="email" name="submit_by" id="submit_by" required/>
                        </div>
                        <div class="field">
                            <label for="adres">Adres</label>
                            <input type="text" name="straat" id="straat" required/>
                        </div>
                        <div class="field">
                            <label for="postcode">Postcode</label>
                            <input type="text" name="postcode" id="postcode" required/>
                        </div>
                        <div class="field">
                            <label for="woonplaats">Woonplaats</label>
                            <input type="text" name="plaats" id="plaats" required/>
                        </div>
                        <div class="field">
                            <label for="telefoon">Telefoon</label>
                            <input type="text" name="telefoon" id="telefoon"/>
                        </div>
                        <div class="field">
                            <input type="checkbox" id="interesse1" name="interesse1"
                                   value="Ik wil een informatiepakket op bovenstaand adres">
                            <label for="interesse1">Ik wil een informatiepakket op bovenstaand adres</label>
                        </div>
                        <div class="field">
                            <input type="checkbox" id="interesse2" name="interesse2"
                                   value="Ik kom donderdagavond eten bij C.S.R.">
                            <label for="interesse2">Ik kom donderdagavond eten bij C.S.R. (Vul hieronder datum en evt. dieet
                                in)</label>
                        </div>
                        <div class="field">
                            <input type="checkbox" id="interesse3" name="interesse3"
                                   value="Ik wil graag meelopen met een C.S.R.'er">
                            <label for="interesse3">Ik wil graag meelopen met een C.S.R.'er (Vul hieronder je studie,
                                onderwijsinstelling en een voorkeurdatum in)</label>
                        </div>
                        <div class="field">
                            <input type="checkbox" id="interesse4" name="interesse4"
                                   value="Ik wil donderdag graag langskomen op een borrel">
                            <label for="interesse4">Ik wil donderdag graag langskomen op een borrel (Vul hieronder datum
                                in)</label>
                        </div>
                        <div class="field">
                            <label for="opmerking">Opmerking</label>
                            <textarea name="opmerking" id="opmerking" rows="4"></textarea>
                        </div>
                        <div class="field">
                            <div class="g-recaptcha" data-sitekey="6Lc9TCITAAAAAGglcvgYvSwL-ci4A3Hkv8s1xRIX"></div>
                        </div>
                        <ul class="actions">
                            <li><input type="submit" value="Verzenden"/></li>
                        </ul>
                    </form>
