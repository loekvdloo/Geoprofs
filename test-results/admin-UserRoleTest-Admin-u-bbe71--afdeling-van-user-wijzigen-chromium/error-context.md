# Page snapshot

```yaml
- generic [ref=e3]:
  - banner [ref=e4]:
    - link "GeoProfs" [ref=e5] [cursor=pointer]:
      - /url: /dashboard
      - heading "GeoProfs" [level=1] [ref=e6]
    - navigation [ref=e7]:
      - link "Verlof" [ref=e8] [cursor=pointer]:
        - /url: /verlof
      - link "Records" [ref=e9] [cursor=pointer]:
        - /url: /records
      - link "Gebruikers" [ref=e10] [cursor=pointer]:
        - /url: /admin/users
    - generic [ref=e12]:
      - link "Medewerker Test" [ref=e13] [cursor=pointer]:
        - /url: /profile/edit
      - button "Logout" [ref=e14] [cursor=pointer]
  - main [ref=e15]:
    - generic [ref=e16]:
      - 'heading "Gebruiker beheren: Admin Geo" [level=1] [ref=e17]'
      - generic [ref=e18]:
        - generic [ref=e19]: Rol
        - combobox [ref=e20]:
          - option "Admin"
          - option "Medewerker" [selected]
          - option "manager"
      - generic [ref=e21]:
        - generic [ref=e22]: Afdeling
        - combobox [ref=e23]:
          - option "HR"
          - option "Finance"
          - option "IT" [selected]
      - button "Opslaan" [ref=e24] [cursor=pointer]
      - paragraph [ref=e25]: Rol en afdeling bijgewerkt
```