<!--- BEGIN HEADER -->
# Changelog

All notable changes to this project will be documented in this file.
<!--- END HEADER -->

## [2.18.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.17.0...v2.18.0) (2024-11-14)

### Features

* Use teamcraft's data for item names ([430a9e](https://code.itinerare.net/itinerare/ffxiv-tools/commit/430a9eb7f0675d28b42b75c289a7ce2a1c9cfc21))

##### Commands

* Add command to prune erroneous recipes ([847a66](https://code.itinerare.net/itinerare/ffxiv-tools/commit/847a6614f5a963240787702a6f873d8902c5169f))

##### Tests

* Update crafting, gathering tests ([055f22](https://code.itinerare.net/itinerare/ffxiv-tools/commit/055f222512fd4a6fa95dfbc3f9b8fd898a351b0f))
* Update diadem tests ([2ae4b7](https://code.itinerare.net/itinerare/ffxiv-tools/commit/2ae4b7cc04c9ccacdb3cfb2410a79f32dffb349f))

### Bug Fixes

* Better handle unset checkboxes when handling settings cookie ([e4a6c3](https://code.itinerare.net/itinerare/ffxiv-tools/commit/e4a6c39b466fecb9e2b1d2d786589c3ee5a26537))
* Check for game item existence before showing universalis etc buttons ([1cd512](https://code.itinerare.net/itinerare/ffxiv-tools/commit/1cd512ec4d506ba27dc26a4977f6c502df5ecf28))
* Check if request value is not null in settings cookie handling ([42a776](https://code.itinerare.net/itinerare/ffxiv-tools/commit/42a77696254b33c0b0d2f7fbaf84171be4b32d96))
* Clearer checks for if vars are set ([6931d8](https://code.itinerare.net/itinerare/ffxiv-tools/commit/6931d8c8ecbb4bc477d38d07bd5592695352feb7))
* Ensure controller vars are defined or have a fallback ([cf1d80](https://code.itinerare.net/itinerare/ffxiv-tools/commit/cf1d803a26758e6abe2e574ee5d5b8461422d4f2))
* Sanity check recipe values before recording ([913eb0](https://code.itinerare.net/itinerare/ffxiv-tools/commit/913eb04fa3f7c8fe2b4eaae3e9c57f7c498959e6))

##### Crafting

* Get range count from config when paginating ([534386](https://code.itinerare.net/itinerare/ffxiv-tools/commit/534386e50d807e2509cbf383a55aa36c287ad34a))
* Improve universalis/teamcraft button display on small screens/mobile ([84965b](https://code.itinerare.net/itinerare/ffxiv-tools/commit/84965b2c318beb544a53a84bfe1214c220c891a2))

##### Leveling

* Explicitly disable use lodestone before handling settings cookie ([8073cb](https://code.itinerare.net/itinerare/ffxiv-tools/commit/8073cbfe4823a7a1aa506dcbb990fc9e4e1e9e9f))

##### Tests

* Add missing cookie assertions to crafting tests ([f85182](https://code.itinerare.net/itinerare/ffxiv-tools/commit/f8518213f7b9b15dddf04bea4365c9fcdd205887))


---

## [2.17.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.16.0...v2.17.0) (2024-10-11)

### Features

* Add maintenance view ([a3ba94](https://code.itinerare.net/itinerare/ffxiv-tools/commit/a3ba949fd9307584178722cf6ee185e95067fd2c))

##### Diadem

* Improve recommendation logic ([dbd21e](https://code.itinerare.net/itinerare/ffxiv-tools/commit/dbd21ecca8b04e83c69f526997d57adf816b751f))
* Store world setting via cookie ([e5a18f](https://code.itinerare.net/itinerare/ffxiv-tools/commit/e5a18fb713ba4b765b43711b763d6bae4c7e1fa4))

### Bug Fixes

* Don't use layout for 503 view ([905053](https://code.itinerare.net/itinerare/ffxiv-tools/commit/9050535ed59c409f4241f9c117d7b2aad0620294))

##### Crafting

* Use validation rule array for cookie construction ([bc2252](https://code.itinerare.net/itinerare/ffxiv-tools/commit/bc225209e5e56aa60631bec2e780ba6c85bb1c54))


---

## [2.16.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.15.0...v2.16.0) (2024-09-29)

### Features

* Add cookie storage for settings; closes #14 ([1049f1](https://code.itinerare.net/itinerare/ffxiv-tools/commit/1049f1a6e76f742d38333d69ef300d9ceb29476b))


---

## [2.15.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.14.0...v2.15.0) (2024-08-09)

### Features

* Add flash messages, automatic page refresh when queueing universalis cache update job ([264500](https://code.itinerare.net/itinerare/ffxiv-tools/commit/2645001d1c9f3a0574096d99b7bfe9220862d376))
* Make universalis data cache lifetime/rate limit configurable ([ea0d10](https://code.itinerare.net/itinerare/ffxiv-tools/commit/ea0d10fa61bdd6aa5d80829d5f37c9955c790dae))
* Move record filtering to Universalis update dispatch job ([f91055](https://code.itinerare.net/itinerare/ffxiv-tools/commit/f91055bfa6f8b54a6f32e5252cd20bf92fa0d11c))
* Move universalis cache update, flash to own function; only dispatch job if necessary ([010fb8](https://code.itinerare.net/itinerare/ffxiv-tools/commit/010fb8962149073debda1dbd8de08f4c3ee6f7fc))
* Reduce default Universalis rate limit lifetime to 45 minutes ([31ddeb](https://code.itinerare.net/itinerare/ffxiv-tools/commit/31ddebf36d010c919bc72a82d1d2befae0f2930f))

##### Crafting

* Add "min profit per" setting ([8c38d5](https://code.itinerare.net/itinerare/ffxiv-tools/commit/8c38d59f98c7cb57732e0090c66704b8ef84841c))
* Filter out recipes disqualified from recommended before ordering ([4ed682](https://code.itinerare.net/itinerare/ffxiv-tools/commit/4ed682eb94175af418caa85224c013415b4dfc95))
* Weight recommended recipes by data age ([205077](https://code.itinerare.net/itinerare/ffxiv-tools/commit/205077d89bde1bfdd52ccf65b939e3e484317e05))

##### Gathering

* Filter out items disqualified from recommended before ordering ([d49f54](https://code.itinerare.net/itinerare/ffxiv-tools/commit/d49f5499a3c25e40df003b30b23fce19901a8c04))
* Weight recommended items by data age ([5d2964](https://code.itinerare.net/itinerare/ffxiv-tools/commit/5d29640904353496618afdd2cbfeff41d75b3a76))

### Bug Fixes


##### Crafting

* Persist min profit setting across world changes ([571e8f](https://code.itinerare.net/itinerare/ffxiv-tools/commit/571e8f35c84c7bfcd0dc72fd60879ca21bc52488))

##### Tests

* Don't check for universalis cache update job ([b368b7](https://code.itinerare.net/itinerare/ffxiv-tools/commit/b368b704bfc86b0c537bd5c72c69aa3e065e940c))


---

## [2.14.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.13.0...v2.14.0) (2024-08-07)

### Features

* Add Universalis, Teamcraft URL accessors to game item model ([f25021](https://code.itinerare.net/itinerare/ffxiv-tools/commit/f250212bead311edbd8fe9a754a14d851a7a7179))

##### Crafting

* Add Universalis links to recipes ([95fedf](https://code.itinerare.net/itinerare/ffxiv-tools/commit/95fedf4a31d3836fbf1a75a337cc8438f21fd7f1))

##### Gathering

* Add Universalis links to items ([821022](https://code.itinerare.net/itinerare/ffxiv-tools/commit/821022e0f1fb42b738244fce5067b46b4ce737ab))

### Bug Fixes

* Link to changelog as of tagged version ([857068](https://code.itinerare.net/itinerare/ffxiv-tools/commit/8570687e8908dd857346a7d7186287d6a6d0559f))

##### Gathering

* Don't recommend items with data older than 12 hours ([3efdd5](https://code.itinerare.net/itinerare/ffxiv-tools/commit/3efdd588320ac7cea071049422118ff15575f2ef))


---

## [2.13.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.12.0...v2.13.0) (2024-08-04)

### Features


##### Crafting

* Rank recipes by sale velocity weighted by price (rather than the reverse) ([0d39d7](https://code.itinerare.net/itinerare/ffxiv-tools/commit/0d39d75a78bc144d2b30ec65af6786bfabff4e47))

### Bug Fixes


##### Crafting

* Don't recommend recipes with data older than 12 hours ([e2c087](https://code.itinerare.net/itinerare/ffxiv-tools/commit/e2c08715a2fcc6a85d6dc6f20b776f49e8ee35f1))


---

## [2.12.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.11.0...v2.12.0) (2024-08-01)

### Features


##### Gathering

* Add basic gathering profit calculator ([f399f8](https://code.itinerare.net/itinerare/ffxiv-tools/commit/f399f81c7f040f0cb8434fbf24dd6483074acd3a))

### Bug Fixes


##### Gathering

* Fix unrestricted material filter ([fa9aaf](https://code.itinerare.net/itinerare/ffxiv-tools/commit/fa9aaf8b84182896f178ef84990ebb35fb87a505))
* Format recommended item price numbers ([889ceb](https://code.itinerare.net/itinerare/ffxiv-tools/commit/889ceb098a44f0405286bdce0a4c156cbb079743))
* Tweak list division to try to be more even ([ae758c](https://code.itinerare.net/itinerare/ffxiv-tools/commit/ae758ca18049091750fcd19febcc3673433f5a8b))


---

## [2.11.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.10.0...v2.11.0) (2024-08-01)

### Features

* Add changelog to footer ([0d4e60](https://code.itinerare.net/itinerare/ffxiv-tools/commit/0d4e609d7b398fdadd0e2db2609cbfe0e33f9da2))

##### Crafting

* Add option to ignore master recipes ([76529b](https://code.itinerare.net/itinerare/ffxiv-tools/commit/76529b175dbc2ea4184615fb6a647f754ad56954))
* Add setting to include/ignore aethersand costs ([6a2bb7](https://code.itinerare.net/itinerare/ffxiv-tools/commit/6a2bb706f464f0b863931fe672e0f5ae506b6f20))
* Do not recommend recipes with no sale velocity ([915a5c](https://code.itinerare.net/itinerare/ffxiv-tools/commit/915a5c5664eb8aabf3952518e9cc51bc3823b893))

### Bug Fixes


##### Crafting

* Only return profit number(s) if there's non-zero price data ([189371](https://code.itinerare.net/itinerare/ffxiv-tools/commit/189371163d1b2e9cbe4a98c8991c4d573ed32a32))


---

## [2.10.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.9.0...v2.10.0) (2024-07-30)

### Features

* Add shop data to game items ([f4f548](https://code.itinerare.net/itinerare/ffxiv-tools/commit/f4f548f03f30c9471e39f51d460c93c97338d327))

##### Crafting

* Add effective cost to ingredient list ([83705f](https://code.itinerare.net/itinerare/ffxiv-tools/commit/83705ff47263aee07c6719d351c096a46d47fbbd))
* Directly check if recipe result is tradeable or not ([231e8f](https://code.itinerare.net/itinerare/ffxiv-tools/commit/231e8f58ab4c2f5fc365dbdc8d5f500bd33cc3d8))
* Display items available from a vendor in ingredient list ([fb8313](https://code.itinerare.net/itinerare/ffxiv-tools/commit/fb831313a67d03600b698b46c7dbe21a57ee6002))
* Display recipe count in pagination info ([30ff69](https://code.itinerare.net/itinerare/ffxiv-tools/commit/30ff69dd6f78e443252ae469e8547b2291742a5b))
* Store "can HQ" for recipes ([c14022](https://code.itinerare.net/itinerare/ffxiv-tools/commit/c140228252317f99c0f75e3cd83b81c6b1a1df1a))
* Use shop data in cost calculation ([d18b63](https://code.itinerare.net/itinerare/ffxiv-tools/commit/d18b631119c112c1b2d81cd916ab6f4a3b0469b8))

### Bug Fixes

* Allow by passing wait time for universalis data with a nq price of 0 ([e1bc27](https://code.itinerare.net/itinerare/ffxiv-tools/commit/e1bc27ee4373dc411f413c4d871674c928941379))
* Cast shop data to array ([cbca91](https://code.itinerare.net/itinerare/ffxiv-tools/commit/cbca91160bc5843ca9c52cc295c3f67dee279bfe))

##### Crafting

* Adjust recipe filters to be more permissive ([b99655](https://code.itinerare.net/itinerare/ffxiv-tools/commit/b9965534f59d792c28bd2d812e7f08310f641dc7))
* Allow recipe processing to update game items without names set ([97daea](https://code.itinerare.net/itinerare/ffxiv-tools/commit/97daea615d3c6514881710eeb878c40e92dd53c2))
* Better check if game item exists before getting shop data ([ea9328](https://code.itinerare.net/itinerare/ffxiv-tools/commit/ea9328d80e571153ad2696dae129e8e89a1db672))
* Don't display recommended recipe HQ sale velocity for no-HQ recipes ([608c20](https://code.itinerare.net/itinerare/ffxiv-tools/commit/608c20f6967f267699252910c0bffb25bc9bf181))
* Don't show cost/profit unless sufficient data ([288f32](https://code.itinerare.net/itinerare/ffxiv-tools/commit/288f323aa32d7c8133b574cf14d1bec234629469))
* Handle profit calc failure better in display function ([3d5366](https://code.itinerare.net/itinerare/ffxiv-tools/commit/3d53662dff8b6d07ddd832ba022b8f141f9c368c))
* Only use HQ numbers for recommendation sorting if recipe can HQ ([87c4bb](https://code.itinerare.net/itinerare/ffxiv-tools/commit/87c4bb144c175a6851e6107b9148c38c04daa083))
* Use clearer fallbacks for unknown item names ([7c85c0](https://code.itinerare.net/itinerare/ffxiv-tools/commit/7c85c053087855d671635ee9aa2a3ba3678faf18))


---

## [2.9.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.8.0...v2.9.0) (2024-07-29)

### Features

* Add CUL profit calc to navbar, collect economy tools into a dropdown ([2bf197](https://code.itinerare.net/itinerare/ffxiv-tools/commit/2bf197d5e747e2bd8af62e57014556637424f16f))
* Add mob drop, gatherable info to game items ([c22094](https://code.itinerare.net/itinerare/ffxiv-tools/commit/c220946e198c66b7c2d78a727b93e7e207680570))
* Eager load game item info for recipes, universalis data ([5b71e7](https://code.itinerare.net/itinerare/ffxiv-tools/commit/5b71e7d417620a7118be6481ab5a1d3376ee194b))
* Move price display to unified widget ([f46187](https://code.itinerare.net/itinerare/ffxiv-tools/commit/f46187aab82d7392977f7454319874f6dc408100))
* Request multiple items' info per XIVAPI request ([64ef81](https://code.itinerare.net/itinerare/ffxiv-tools/commit/64ef819f7c763f0c64f795c52b1b22e9631dd8cc))

##### Commands

* Only queue universalis update jobs from command in production ([89285c](https://code.itinerare.net/itinerare/ffxiv-tools/commit/89285cfecd65c7714ee47104108f5daa61ba47b9))

##### Crafting

* Add basic pagination for ranges ([e37fe4](https://code.itinerare.net/itinerare/ffxiv-tools/commit/e37fe4508d3ef6fd0054ac31165e00a285d9fe8d))
* Add crafting profit calculator page ([a7cdbc](https://code.itinerare.net/itinerare/ffxiv-tools/commit/a7cdbc55c50a66870712ce88ab64357c2a599d98))
* Add recipe handling and storage, retrieval ([11a3af](https://code.itinerare.net/itinerare/ffxiv-tools/commit/11a3af1d8e3f88dc11cf749111edcf14d5431255))
* Display recipe job on precrafts if different from selected job ([a11b96](https://code.itinerare.net/itinerare/ffxiv-tools/commit/a11b96004404c77c2f1a4937bd56f977d2bd6646))
* Eager load price, game item data; condense ingredient data queries ([caf8c2](https://code.itinerare.net/itinerare/ffxiv-tools/commit/caf8c2c197ed266eabe4e634728dff44250e4f0c))
* Handle all jobs' recipes ([3cc0bb](https://code.itinerare.net/itinerare/ffxiv-tools/commit/3cc0bb00b5d8d33fac61a7cb25e6ee0c94ca2f11))
* Include sale velocity in top recipes display ([ddadee](https://code.itinerare.net/itinerare/ffxiv-tools/commit/ddadee00b7c6508f0dee5473ab42ff8e37979535))
* Move profit display to own widget ([494d9a](https://code.itinerare.net/itinerare/ffxiv-tools/commit/494d9a3fef482e1ef2bb4495c8283dd9352343d1))
* Validate settings input ([4bbb59](https://code.itinerare.net/itinerare/ffxiv-tools/commit/4bbb59f1a052e4604380582801ffdd6f0f66f381))
* Weight top recipes slightly by HQ trade velocity ([a9ebe9](https://code.itinerare.net/itinerare/ffxiv-tools/commit/a9ebe9abf726e24b35a50d734ac0c7e04d767d4b))

##### Tests

* Add crafting tests ([231d02](https://code.itinerare.net/itinerare/ffxiv-tools/commit/231d02666d9c5fd724b57d1cb2acf884aa8886cf))

### Bug Fixes

* Add additional safeties to game item processing ([64171b](https://code.itinerare.net/itinerare/ffxiv-tools/commit/64171b595ad860f08302ab58c64f861029ce8029))
* Allow game item update jobs to overlap ([3b0b88](https://code.itinerare.net/itinerare/ffxiv-tools/commit/3b0b88e20661e47a9ae2c26ca9f6a574c4d09195))
* Collapse world select if a world is selected ([179371](https://code.itinerare.net/itinerare/ffxiv-tools/commit/179371ad1a60018a1ba26df05e38a5318536bdf0))
* Display HQ trade velocity first if shown ([80b9ee](https://code.itinerare.net/itinerare/ffxiv-tools/commit/80b9ee0143b6b93768c173e96b67cac8afd7b981))
* Do not always eager-load game item with universalis record ([37f665](https://code.itinerare.net/itinerare/ffxiv-tools/commit/37f66564022081d5f72039eccb42c9a93502d6c6))
* More clearly handle nonexistent universalis record update time ([9ebd6a](https://code.itinerare.net/itinerare/ffxiv-tools/commit/9ebd6adf6b93cbee5cb27ed6a3a6afb6fcb921bf))

##### Crafting

* Only dispatch game item/universalis record create jobs if items to process ([ed9008](https://code.itinerare.net/itinerare/ffxiv-tools/commit/ed900876e9737cd97c1aea9a360dc0268d23e87a))
* Only initialize recipe items for which game item or universalis records do not yet exist ([d5882b](https://code.itinerare.net/itinerare/ffxiv-tools/commit/d5882bc28ab26d596836c6e81a1897eafa5d03f8))
* Put separator back in profit display ([1e239e](https://code.itinerare.net/itinerare/ffxiv-tools/commit/1e239e45813f6b8c0dc315573230eec3ef0e5d62))
* Use more specific variable in recipe handling job ([98f2d1](https://code.itinerare.net/itinerare/ffxiv-tools/commit/98f2d173a4a0f07ab1b00b15e6d372355b3ba79b))
* When recording recipes, only dispatch game item/universalis record create jobs if needed ([19a10d](https://code.itinerare.net/itinerare/ffxiv-tools/commit/19a10dd17b2b2fc5c94e464450681f174ab81263))


---

## [2.8.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.7.0...v2.8.0) (2024-07-20)

### Features

* Add fallback display for game item names ([72789a](https://code.itinerare.net/itinerare/ffxiv-tools/commit/72789a10cf1345364b3b916381dbe4e42f63d3b2))
* Get and store Universalis last upload time ([0d20f1](https://code.itinerare.net/itinerare/ffxiv-tools/commit/0d20f1345cd74c46c8ca3594c4e3b29f2576df3d))
* Implement storage for game items, universalis data ([cf83ec](https://code.itinerare.net/itinerare/ffxiv-tools/commit/cf83ec667fee825c4ea9b5867a510b8c291ef944))
* Only get required fields from universalis ([45fdc0](https://code.itinerare.net/itinerare/ffxiv-tools/commit/45fdc01fe83d1568fa2b1b7cd74c2f38b144046c))
* Pre-chunk universalis update input/split into more jobs ([47993b](https://code.itinerare.net/itinerare/ffxiv-tools/commit/47993bc00665aaf7a83d58728bb2d885ce190607))
* Rate-limit universalis cache updates per world ([3f7ec7](https://code.itinerare.net/itinerare/ffxiv-tools/commit/3f7ec711ea21909c89f3fef759457bef01e34b24))
* Universalis caching (!124) ([2801aa](https://code.itinerare.net/itinerare/ffxiv-tools/commit/2801aa426436628515498e71fc231cd25165d3ce))

##### Commands

* Add game item/Universalis cache pruning to cache update command ([a9ac51](https://code.itinerare.net/itinerare/ffxiv-tools/commit/a9ac51680633db8e8af030df13e1a76ecbe1eca7))

##### Diadem

* Display last upload time, relabel retrieval time ([77a4b4](https://code.itinerare.net/itinerare/ffxiv-tools/commit/77a4b492ac590cbe93dcbe2dbf76797460e9d30c))
* Format prices/sales per day ([305972](https://code.itinerare.net/itinerare/ffxiv-tools/commit/3059728a9764884f88a895654396782963306c93))
* Queue universalis cache updates on load ([db69bf](https://code.itinerare.net/itinerare/ffxiv-tools/commit/db69bff2cb655b87fe018a9fc6c8a570686fbf1f))
* Update to use stored universalis data ([24271d](https://code.itinerare.net/itinerare/ffxiv-tools/commit/24271d6598ebba626653aa36b0ba8ad3932eaaab))
* Use node data directly ([9e91c5](https://code.itinerare.net/itinerare/ffxiv-tools/commit/9e91c5dcdac577bd3db0e68a01195c69ec41728f))

##### Tests

* Add game item/universalis job tests ([659873](https://code.itinerare.net/itinerare/ffxiv-tools/commit/6598739eeded81ac486268aac22951b5cf52882a))
* Add initialized state to diadem tests ([e5107e](https://code.itinerare.net/itinerare/ffxiv-tools/commit/e5107e4c20b25f93a69b218c2c15cdaf6d9c5f31))
* Fake queue in diadem tests ([239ef8](https://code.itinerare.net/itinerare/ffxiv-tools/commit/239ef82b4fa979053954008554de6625e0e64ddb))

### Bug Fixes

* Better universalis command progress bar calculation ([bb8f53](https://code.itinerare.net/itinerare/ffxiv-tools/commit/bb8f53d70f59bf9f571c7b0c90158bca1af3e741))
* Remove safety from universalis cache update function ([8ef973](https://code.itinerare.net/itinerare/ffxiv-tools/commit/8ef97331fd8174d23581aa8fcf7e9f98c416d177))

##### Commands

* Better progress bar calc in universalis update command ([c13ad3](https://code.itinerare.net/itinerare/ffxiv-tools/commit/c13ad311c8b3dd98c777f424b91104fb96b1f2a3))

##### Diadem

* Check for game item existence before loading items ([39e83d](https://code.itinerare.net/itinerare/ffxiv-tools/commit/39e83da9b6a1540c98e41de5098547bc9a32fc65))
* Fix getting prices from cache record ([7e20a3](https://code.itinerare.net/itinerare/ffxiv-tools/commit/7e20a3079822aa40506bf3df6cd602427a250e23))
* Fix price/sale velocity placeholder display ([308642](https://code.itinerare.net/itinerare/ffxiv-tools/commit/3086429164191a898ef1c388ba68144ecb4af6a4))

##### Tests

* Basic fix for existing diadem tests ([c38eff](https://code.itinerare.net/itinerare/ffxiv-tools/commit/c38eff6d2897862f5380b0ad470fb3570a4aaff0))


---

## [2.7.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.6.0...v2.7.0) (2024-07-10)

### Bug Fixes


##### Leveling

* Fix rested exp % used calculation ([24c6a7](https://code.itinerare.net/itinerare/ffxiv-tools/commit/24c6a750ccf31b9d9bcb2f47bb4b987a7885e463))


---

## [2.6.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.5.0...v2.6.0) (2024-07-07)

### Features

* Add support for new worlds ([386ef8](https://code.itinerare.net/itinerare/ffxiv-tools/commit/386ef85599161aaf7f5f469029a959d011a5528d))

##### Leveling

* Add 91-99 dungeon EXP values ([dcbf4c](https://code.itinerare.net/itinerare/ffxiv-tools/commit/dcbf4c993d1fc47863727ceb9cd18b7f3910a75e))
* Add EXP to level for 90-99 ([9cf342](https://code.itinerare.net/itinerare/ffxiv-tools/commit/9cf342139b4011923681b2e3a470c8cf69ed3567))
* Add frontline EXP values for 90-99 ([e76e98](https://code.itinerare.net/itinerare/ffxiv-tools/commit/e76e98e6d9b05ecd05e3fe1e901ad9db81cf6506))
* Add support for Dawntrail jobs ([2a1029](https://code.itinerare.net/itinerare/ffxiv-tools/commit/2a1029ce9335605411ba4da43ce7f7b9fe32c1dc))
* Update 91-100 leveling notes ([687d7d](https://code.itinerare.net/itinerare/ffxiv-tools/commit/687d7d6fe5b33c70322194a97ad1a61e59cf0f0d))
* Update level cap to 100 ([fbabae](https://code.itinerare.net/itinerare/ffxiv-tools/commit/fbabae4cd1ca842ead5cfcd1b42a1fe03d6739a6))

##### Tests

* Update leveling tests for new level cap ([0211b8](https://code.itinerare.net/itinerare/ffxiv-tools/commit/0211b89bf564515272bdc7587139d3b5a4e099ad))

### Bug Fixes


##### Leveling

* Add extra checks for presence of data ([6f7021](https://code.itinerare.net/itinerare/ffxiv-tools/commit/6f70213e9802f3be9caa62c8bf98284a06e60bd2))
* Always set avg. exp for frontline ([0edb5d](https://code.itinerare.net/itinerare/ffxiv-tools/commit/0edb5d2ea934c1ff181ca6b889d88378cdda809f))
* Use ceiling for dungeon EXP values ([74a554](https://code.itinerare.net/itinerare/ffxiv-tools/commit/74a554fc9d2953c3a96798f9f052bb40b86d826f))


---

## [2.5.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.4.0...v2.5.0) (2024-05-27)

### Features

* Use bootstrap tooltips ([eec0c1](https://code.itinerare.net/itinerare/ffxiv-tools/commit/eec0c15b7011dce8695ce12da423567100a88301))

##### Leveling

* More informative rested EXP boost info ([aa4d34](https://code.itinerare.net/itinerare/ffxiv-tools/commit/aa4d3446f8420e8b099cb8652435f01823b8b4bd))

### Bug Fixes


##### Leveling

* Make all rested EXP info per-run for consistency ([f9c5b2](https://code.itinerare.net/itinerare/ffxiv-tools/commit/f9c5b203f72f7350825e365318ba655e77942fd9))
* Make FC bonus impact armoury bonus multiplicatively ([35debe](https://code.itinerare.net/itinerare/ffxiv-tools/commit/35debe35f2ddf121e3e405eee13cbcd954c03d12))
* Only show 30 and below bonus % if different from 80 and below bonus % ([02f8a3](https://code.itinerare.net/itinerare/ffxiv-tools/commit/02f8a3556a49e51007b5d112ce771d7af4a9773b))


---

## [2.4.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.3.0...v2.4.0) (2024-05-21)

### Features


##### Leveling

* Add estimated rested EXP calculation ([511f9c](https://code.itinerare.net/itinerare/ffxiv-tools/commit/511f9ccf520cc3d3ae810e6a54042e1aa95dc78d))
* Add rested EXP boosted indicator ([d6766a](https://code.itinerare.net/itinerare/ffxiv-tools/commit/d6766a22d5a9afa2984abf32659327e8ee53abea))
* Display rested EXP used per level in info tooltip ([c6f6f8](https://code.itinerare.net/itinerare/ffxiv-tools/commit/c6f6f885c03cf7607f65fa114bad356bfdb430a4))

##### Tests

* Update leveling tests ([49b531](https://code.itinerare.net/itinerare/ffxiv-tools/commit/49b531f8d98068007bffc831c4f0acf94323e5b2))

### Bug Fixes


##### Leveling

* Clearer and more robust handling for 0 run levels ([2ddb3c](https://code.itinerare.net/itinerare/ffxiv-tools/commit/2ddb3ced94861167ced8b7da34f03b395c3289f5))
* Data not displayed for 82-90 ([6a6dcf](https://code.itinerare.net/itinerare/ffxiv-tools/commit/6a6dcfcb5a5c4370001ec918a251d671ebf4f69b))
* Fix dungeon EXP bonus conversion from percent ([89e102](https://code.itinerare.net/itinerare/ffxiv-tools/commit/89e102413c3813342c8fd70ee4aa41ae9b52a6a1))
* Improve handling of 0 run levels ([e3c7a0](https://code.itinerare.net/itinerare/ffxiv-tools/commit/e3c7a0cfa8d35a136eaa4af850390abbbf611a1a))
* Improve handling of overage ([411a76](https://code.itinerare.net/itinerare/ffxiv-tools/commit/411a7614c0d6ba5d1546b551211e6f22da8fd442))
* Improve rested EXP pool use calculation ([85de0a](https://code.itinerare.net/itinerare/ffxiv-tools/commit/85de0a6a2a8c826ffca031d1c7f066f5664ad7ac))
* Make rested EXP boost indicator more noticeable ([e9d0ba](https://code.itinerare.net/itinerare/ffxiv-tools/commit/e9d0bafe80489bba12d5a6dcfbcd5cdcb8a67fd5))
* Make road label text use level cap config ([e0041b](https://code.itinerare.net/itinerare/ffxiv-tools/commit/e0041bc814f1d14e1636f56c100720654e09e4c6))
* Refine check for rested boost ([832776](https://code.itinerare.net/itinerare/ffxiv-tools/commit/83277665d51ff4a407e36d620c93a14112c8e56b))


---

## [2.3.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.2.0...v2.3.0) (2024-04-06)

### Features

* Set up flashing validation errors ([f8f3ff](https://code.itinerare.net/itinerare/ffxiv-tools/commit/f8f3ff6eb2e6c78810757605f0d664ba811bc805))

### Bug Fixes


##### Leveling

* Fix typo ([338c97](https://code.itinerare.net/itinerare/ffxiv-tools/commit/338c97a1f6f8be826199198443822cd4fe7eb098))
* Validate inputs ([d3e439](https://code.itinerare.net/itinerare/ffxiv-tools/commit/d3e439a4d1c4d79870b610b43611267e5367d191))

##### Tests

* Remove erroneous/redundant leveling test cases ([ef94ce](https://code.itinerare.net/itinerare/ffxiv-tools/commit/ef94ced74e39eda21b252d4714845bb60364077d))
* Update leveling tests ([6452cc](https://code.itinerare.net/itinerare/ffxiv-tools/commit/6452ccfe3ddefcc410bb864af97d9ef5de44df40))


---

## [2.2.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.1.0...v2.2.0) (2024-03-27)

### Features

* Add favicon ([c44017](https://code.itinerare.net/itinerare/ffxiv-tools/commit/c4401794484d956e467d39b0ef5f466192fc2395))

##### Leveling

* Add EO data for level 90 ([05a532](https://code.itinerare.net/itinerare/ffxiv-tools/commit/05a532b72b7d08a2c6817321d178e678d367b125))
* Make earring name and bonus level cap configurable ([d3abcd](https://code.itinerare.net/itinerare/ffxiv-tools/commit/d3abcd381d2aefceb68ab7549189f0ccc75c1749))
* Make last-updated-for patch, motd configurable ([32324c](https://code.itinerare.net/itinerare/ffxiv-tools/commit/32324ce6742ff267b6c1a8f2fa1645d1609dde71))
* Update for 6.58 ([ae9fe9](https://code.itinerare.net/itinerare/ffxiv-tools/commit/ae9fe96f6608839af80b4656991f3cf079f00392))

### Bug Fixes


##### Leveling

* Better safeguards for incomplete data ([a47ebc](https://code.itinerare.net/itinerare/ffxiv-tools/commit/a47ebcc2a215fb624c19105dc808b3f67fba834a))
* Revert level range bonus change ([75eb0e](https://code.itinerare.net/itinerare/ffxiv-tools/commit/75eb0e745a724cd93ddfc3b0285da097be955c66))
* Use level cap config value for deep dungeon bonus calculations ([2d202b](https://code.itinerare.net/itinerare/ffxiv-tools/commit/2d202b38ca2a9bb5fdc12d20774a50ebfc9712e9))
* Use mult of ten for last level range bonus ([62f2fe](https://code.itinerare.net/itinerare/ffxiv-tools/commit/62f2fee04282ffdc91c95d7185892bf375498068))


---

## [2.1.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v2.0.0...v2.1.0) (2024-03-16)

### Features


##### Tests

* Add lodestone data tests to leveling tests ([0af447](https://code.itinerare.net/itinerare/ffxiv-tools/commit/0af447611ba5c81df5544218ec197b12fb25bf9d))
* Update to PHPUnit 11 ([26c9bf](https://code.itinerare.net/itinerare/ffxiv-tools/commit/26c9bf96e64b76a00cc0c13aa9e661015ba24f07))

### Bug Fixes


##### Leveling

* Better check for non-zero level from lodestone data ([54dc80](https://code.itinerare.net/itinerare/ffxiv-tools/commit/54dc809ac8190b65bbaadd28741a6fb2dd462711))
* More clearly handle non-combat/invalid job entry ([77c332](https://code.itinerare.net/itinerare/ffxiv-tools/commit/77c332db5813a50fe23bbb94d8021fff6a07b74c))
* Prefer job over class in tips verbiage ([07e3f1](https://code.itinerare.net/itinerare/ffxiv-tools/commit/07e3f1bf16e9a70edc059ea2a95dda7f4d20e65c))
* Tweak tips verbiage ([f5a76b](https://code.itinerare.net/itinerare/ffxiv-tools/commit/f5a76b74625f7fd557fb1797ecee29921bb3d4b8))


---

## [2.0.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v1.5.0...v2.0.0) (2024-03-14)

### ⚠ BREAKING CHANGES

* Update to Laravel 11 ([bed684](https://code.itinerare.net/itinerare/ffxiv-tools/commit/bed6842613185f8de7a7fe272637bd66f3d78921))


---

## [1.5.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v1.4.0...v1.5.0) (2024-02-13)

### Features


##### Leveling

* Add support for fetching level/exp data from the lodestone ([81ee17](https://code.itinerare.net/itinerare/ffxiv-tools/commit/81ee171b115843421467a75d11391e70b50604b6))

### Bug Fixes


##### Diadem

* Flash error on Universalis request failure ([448730](https://code.itinerare.net/itinerare/ffxiv-tools/commit/448730bff0655c2fd5c96c2666933ca70bfa5569))

##### Leveling

* Better check for if class/job is set ([f2c4b3](https://code.itinerare.net/itinerare/ffxiv-tools/commit/f2c4b3ced75ff9b00eed4361ec5a76d545aeca50))
* Better error messaging ([8a71dc](https://code.itinerare.net/itinerare/ffxiv-tools/commit/8a71dc7c032229503d9fd388fb9d10b76f686769))
* Only deduct current EXP for current level run/overage calculations ([c1a449](https://code.itinerare.net/itinerare/ffxiv-tools/commit/c1a44955fc80c9e5b9c709a5409ef6a62cc6279d))
* Only set use lodestone if other values in the request are set ([863b66](https://code.itinerare.net/itinerare/ffxiv-tools/commit/863b66754da02897fe22bf6adfd0a56091411728))
* Only unset use_lodestone if unchecked ([285608](https://code.itinerare.net/itinerare/ffxiv-tools/commit/285608e15aeec433165112c488f55ca71e0e9f12))
* Set use_lodestone even if disabled ([680da6](https://code.itinerare.net/itinerare/ffxiv-tools/commit/680da6a1eb819c251ad4ad5b3bff7cf0d2b6f905))


---

## [1.4.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v1.3.0...v1.4.0) (2024-02-03)

### Bug Fixes


##### Diadem

* Better handling for a failed request following a successful one ([1c5820](https://code.itinerare.net/itinerare/ffxiv-tools/commit/1c582079f8db2eba3be22b60eed7687592585b90))
* Clearer error message ([42be0f](https://code.itinerare.net/itinerare/ffxiv-tools/commit/42be0f71e1560ccc39c5b1e5af9af343a1179cab))
* Do not include items with unknown prices in top five ([d9d967](https://code.itinerare.net/itinerare/ffxiv-tools/commit/d9d967af6f57f0433ba3f2de4a06dc00a48cdd5f))
* Provide empty arrays to view instead of null as fallback ([8f1350](https://code.itinerare.net/itinerare/ffxiv-tools/commit/8f1350fd72ce19a6dee08db14d4439023a87e6c1))
* Use more general request exception, check if response is an array ([f26751](https://code.itinerare.net/itinerare/ffxiv-tools/commit/f267516885b8b1cc6c6dac6d206be4f1b5cc81dd))


---

## [1.3.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v1.2.0...v1.3.0) (2024-02-03)

### Features


##### Tests

* Add diadem tests ([307983](https://code.itinerare.net/itinerare/ffxiv-tools/commit/3079834ad9f0fc22292579467e4845f48d64256b))
* Add leveling tests ([6594e2](https://code.itinerare.net/itinerare/ffxiv-tools/commit/6594e2577174b273fce5fbf66fe68cb26d5d84f4))
* Basic setup, index access test ([05e1fb](https://code.itinerare.net/itinerare/ffxiv-tools/commit/05e1fba314f3d9b829199644ba0bec4e54ea9d28))

### Bug Fixes


##### Diadem

* Catch exceptions from universalis better ([47c66b](https://code.itinerare.net/itinerare/ffxiv-tools/commit/47c66b541a943064205156b8702a617ad48a70a5))
* Validate world name ([670220](https://code.itinerare.net/itinerare/ffxiv-tools/commit/6702201a604e695300e8ad6d2449f5fc50250a64))

##### Tests

* Fix diadem test name ([5dcbb2](https://code.itinerare.net/itinerare/ffxiv-tools/commit/5dcbb264e70afb3d8da9b7121ff5c30230a35d00))


---

## [1.2.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v1.1.0...v1.2.0) (2024-02-02)

### Features

* Add date/commit hash to footer as loose version string ([0c3fec](https://code.itinerare.net/itinerare/ffxiv-tools/commit/0c3fec58f35dcd788c3b11e8c59b20494903e0a5))

### Bug Fixes

* Store hash in untracked file ([17cf31](https://code.itinerare.net/itinerare/ffxiv-tools/commit/17cf3181f4ec557466989e672d280a7f4fdca131))
* Store version string in file, add creation to composer dump ([5097ff](https://code.itinerare.net/itinerare/ffxiv-tools/commit/5097ff920959d5013226e3d0f5211969b2efc428))


---

## [1.1.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/v1.0.0...v1.1.0) (2024-01-30)

### Features

* Add nav, set up credit per tool ([0c3e45](https://code.itinerare.net/itinerare/ffxiv-tools/commit/0c3e45b79329eb817c076a6c0c0bc789cec7d943))
* Add support for page titles ([fce378](https://code.itinerare.net/itinerare/ffxiv-tools/commit/fce378dc6a9a66a0dcf187c059e9cafb93d79307))
* Move data center select to own view, support all regions ([365f4e](https://code.itinerare.net/itinerare/ffxiv-tools/commit/365f4e4c7b75e9d43b8405af0b01da3b3a87a817))
* Move diadem info to own page ([b03785](https://code.itinerare.net/itinerare/ffxiv-tools/commit/b03785c0144d3b2589a20a48e2de3a2615caab69))
* Switch to bootstrap light/dark theme, add theme toggle ([d272a7](https://code.itinerare.net/itinerare/ffxiv-tools/commit/d272a764b2bf607f8c4dd1ef8ffc5b3965609309))

##### Leveling

* Add leveling calculator ([a2b5bb](https://code.itinerare.net/itinerare/ffxiv-tools/commit/a2b5bb31dd48e1ac6a40566f972b8d815b394074))

### Bug Fixes

* Add better spacing between navigation/world buttons on small viewports ([ca6480](https://code.itinerare.net/itinerare/ffxiv-tools/commit/ca6480e11c3c0919bae8816b3232885cb12b398d))
* Display diadem view ([a46c4f](https://code.itinerare.net/itinerare/ffxiv-tools/commit/a46c4f5f83d1dd7876d97241eb0179d5e81af19f))
* Make checked checkboxes more readable ([919331](https://code.itinerare.net/itinerare/ffxiv-tools/commit/919331f95be8210db18f99e654ffb4b88a5713e0))

##### Diadem

* Make nav link/page title clearer ([d17b9a](https://code.itinerare.net/itinerare/ffxiv-tools/commit/d17b9a1c692f63d64527f1dbc37e02078adcf743))

##### Leveling

* Add level cap to config, rework calculation/display for future-proofing ([9cc1b9](https://code.itinerare.net/itinerare/ffxiv-tools/commit/9cc1b99edc381dd06df01b3d9b79d5f020446fb7))
* Adjust bonus override text ([43b097](https://code.itinerare.net/itinerare/ffxiv-tools/commit/43b0975e6f5407853ae267bb5e679f891104b8b7))
* Don't use plural form when only one run/match left ([3b605d](https://code.itinerare.net/itinerare/ffxiv-tools/commit/3b605d72e2f8f640b6755d6b6af086ac40ff02ae))
* Format EXP numbers ([a43995](https://code.itinerare.net/itinerare/ffxiv-tools/commit/a4399567fff281d01b470cdc8a87b8cb92f87335))
* No level ranges displayed with settings but no character level ([e1bb12](https://code.itinerare.net/itinerare/ffxiv-tools/commit/e1bb127c8a937c5667cef7d8593f407a87776a5c))
* Put back placeholder on frontline EXP display ([df0eaf](https://code.itinerare.net/itinerare/ffxiv-tools/commit/df0eafa2b8ba387d18725b9adbebdf3aadd62c86))
* Remove redundant ternary ([c3ac63](https://code.itinerare.net/itinerare/ffxiv-tools/commit/c3ac6313bd18182a3b099540c62cff04c9c0e2af))
* Separate out isset from exp value formatting ([8cb9e1](https://code.itinerare.net/itinerare/ffxiv-tools/commit/8cb9e104b4bfd28fc4320651e4ed8b3f2c91f876))
* Switch runs to matches for frontline buttons ([33e22a](https://code.itinerare.net/itinerare/ffxiv-tools/commit/33e22ad2f5019e7f287466e8751af747cd608047))
* Unformat EXP % ([e42b19](https://code.itinerare.net/itinerare/ffxiv-tools/commit/e42b196fb3d7d59cb1fb6c8652149834933a6017))
* Use full deep dungeon names in level range tips ([6fbc79](https://code.itinerare.net/itinerare/ffxiv-tools/commit/6fbc798c7bd6aef34113e9827a8db088c0515d75))


---

## [1.0.0](https://code.itinerare.net/itinerare/ffxiv-tools/compare/1eab19ee1ffa22eafd2e7f09817a1af6503d56e0...v1.0.0) (2023-04-06)

### Features

* Add world select, initial worldless state ([7743cd](https://code.itinerare.net/itinerare/ffxiv-tools/commit/7743cdff04b1a2d32e8826d3b128700222295919))
* Index page with basic organized ranking ([357f83](https://code.itinerare.net/itinerare/ffxiv-tools/commit/357f8348306bf35edabcee19b3b0acabe6bfb136))
* Organize by node ([6621ff](https://code.itinerare.net/itinerare/ffxiv-tools/commit/6621ff69ca3aa3974ec06a02710a1ea986a5fcf1))
* Personal styling ([06df8e](https://code.itinerare.net/itinerare/ffxiv-tools/commit/06df8e44f4fe20c6dc4cf39f9cb1ec3462024a37))

### Bug Fixes

* Extra check if price is set ([fa354b](https://code.itinerare.net/itinerare/ffxiv-tools/commit/fa354b4da7c87d6a4ff189227ce0437a9c24a84c))
* Styling issues/missing fonts ([c094fa](https://code.itinerare.net/itinerare/ffxiv-tools/commit/c094fa9426fa588d91427116d775531db949b86e))


---

