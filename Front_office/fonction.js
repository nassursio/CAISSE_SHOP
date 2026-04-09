

function convertirScan(code){
        return code.replaceAll("Shift", "") 
        .replaceAll("à", "0")
        .replaceAll("&", "1")
        .replaceAll("é", "2")
        .replaceAll("\"", "3")
        .replaceAll("'", "4")
        .replaceAll("(", "5")
        .replaceAll("-", "6")
        .replaceAll("è", "7")
        .replaceAll("_", "8")
        .replaceAll("ç", "9");
}      


function ajouterProduitDansCaisse(){
    const ticketList = document.getElementById("ticketList");
             ticketList.innerHTML += `
          <div class="ticket-item">
            <div class="info">
              <strong>produit1</strong>
              <span>1.99 €</span>
            </div>
            <div class="actions">
            </div>
          </div>
        `;
}

function afficherProduits(){
   const productList = document.getElementById("productGrid");

   for (let index = 0; index < produits.length; index++) {
      productList.innerHTML = productList + `
          <div class="product-card">
            <h3>${produits[index].Nom}</h3>
            <p>${produits[index].Prix.toFixed(2)} €</p>
            <button type="button" onclick="ajouterProduitDansCaisse(${produits[index].id})">Ajouter</button>
          </div>
        `;
   }
}


