// Autocomplete types

// Remote suggest: Externe bron met string waardes, mag ook zelf iets verzinnen
// EntityField: Externe bron met label + id

import {select} from "./dom";
import {autocomplete} from "@algolia/autocomplete-js";
import axios, {AxiosResponse} from "axios";

export function initEntityField(container: HTMLElement): void {
	const {
		id,
		url,
		name,
		valueShow,
		valueId,
		idField,
	} = JSON.parse(container.dataset.entityField)

	const idInput = select<HTMLInputElement>(`#${id}`)

	const label = select<HTMLLabelElement>(`[for=${id}]`)
	label.htmlFor = id + '-input'

	autocomplete({
		container,
		defaultActiveItemId: 0,
		id,
		initialState: {
			query: valueShow
		},
		onReset() {
			idInput.value = '';
		},
		getSources({query}) {
			return axios(`${url}${query}`)
				.then((response: AxiosResponse) => {
					return [
						{
							templates: {
								item({item}) {
									return item.value as string
								},
								noResults() {
									return 'Geen resultaat'
								}
							},
							sourceId: 'keuzes',
							getItems() {
								return response.data
							},
							getItemInputValue({item}) {
								return item.value as string
							},
							onSelect({item}) {
								idInput.value = item[idField] as string
							}
						}
					]
				})
		}
	})
}

export function initRemoteSuggestieField(el: HTMLElement): void {
	const options = JSON.parse(el.dataset.autocomplete)

	const hiddenInput = select<HTMLInputElement>(`#${options.id}`)

	autocomplete({
		container: el,
		placeholder: "Zoek hier",
		onStateChange({state}) {
			hiddenInput.value = state.query
		},
		getSources({query}) {
			return axios(`${options.url}${query}`)
				.then((response: AxiosResponse<{ value: string }[]>) => {
					return [
						{
							templates: {
								item({item}) {
									return item.value as string
								},
								noResults() {
									return 'Geen resultaat'
								}
							},
							sourceId: 'predictions',
							getItems() {
								return response.data;
							},
							getItemInputValue({item}) {
								return item.value as string;
							},
							onSelect({item}) {
								hiddenInput.value = item.id as string
							},

							// ...
						}
					];
				});
		}
	})
}
