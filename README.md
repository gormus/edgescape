# EdgeScape

Content visibility based on geographic location and network characteristics.

This module provides a condition plugin based on the Akamai EdgeScape HTTP 
header value.

The header holds information for many IP attributes, including most popular 
geolocation data;

- **`continent`**

  Two-letter code for the continent associated with the IP address.

- **`country_code`**

  An ISO-3166, two-letter code for the country.

- **`region_code`**

  The state, province, or region code.

- **`city`**

  The city (within a 50-mile radius).

- **`zip`**

  The zipcode (multiple values possible). Only available for the US and Canada.

- **`lat`**

  The latitude.

- **`long`**

  The longitude.


## Examples

IP Address attribute: `country_code`  
Value:  
```
US
GB
```
Negate: Unchecked.

> Condition is `true` for all visitor from the United States, and Great Britain.


IP Address attribute: `continent`  
Value:  
```
EU
```
Negate: Checked.

> Condition is `true` for all visitors except for the continental Europe.




## Akamai EdgeScape

Identify the geographic location and network characteristics of your users.

EdgeScape is Akamai’s IP intelligence service. It enables e-businesses to drive 
targeted business strategies online.

Benefits
- Apply regional download policies to specific countries.

- Provide different types of user experiences dynamically based on a user's 
  geolocation (ie. provide a localized version of a website for people in 
  a given country like www.example.es for people in Spain or www.example.fr 
  for people in France).

- Provide different types of user experiences dynamically based on the user's 
  network connection (ie. cellular/non-cellular, low/high bandwidth, etc.).

- Pre-populate online forms with information relevant to users (ie. a list of 
  phone area codes, ZIP codes, city, state, etc.) based on the IP geolocation 
  of the user.

Read more about the service at https://developer.akamai.com/edgescape
