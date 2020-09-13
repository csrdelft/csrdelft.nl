export interface GroepLid {
	uid: string;
	link: string;
	opmerking2: GroepKeuzeSelectie[];
}

export interface GroepSettings {
	mijn_uid: string;
	mijn_link: string;
	aanmeld_url: string;
}

export interface GroepInstance {
	id: number;
	naam: string;
	familie: string;
	begin_moment: Date;
	eind_moment: Date;
	aanmelden_tot?: Date;
	status: string;
	samenvatting: string;
	omschrijving: string;
	keuzelijst?: null;
	maker_uid: string;
	versie: string;
	keuzelijst2: KeuzeOptie[];
	leden: GroepLid[];
}

export interface KeuzeOptie {
	type: string;
	naam: string;
	default: string;
	opties: string[];
	description: string;
}

export interface GroepKeuzeSelectie {
	naam: string;
	selectie: string;
}
