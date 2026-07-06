#!/usr/bin/env python3
"""Generate StarUML use case diagram (.mdj) for the DDL platform."""

from __future__ import annotations

import base64
import json
import os
from dataclasses import dataclass, field
from typing import Any


def gen_id(prefix: str = "DDL") -> str:
    raw = os.urandom(12)
    return base64.b64encode(raw).decode()


def ref(element_id: str) -> dict[str, str]:
    return {"$ref": element_id}


@dataclass
class Builder:
    project_name: str
    _seq: int = 0

    project_id: str = field(default_factory=gen_id)
    model_id: str = field(default_factory=gen_id)
    diagram_id: str = field(default_factory=gen_id)
    subject_model_id: str = field(default_factory=gen_id)

    model_elements: list[dict[str, Any]] = field(default_factory=list)
    diagram_views: list[dict[str, Any]] = field(default_factory=list)

    actors: dict[str, dict[str, str]] = field(default_factory=dict)
    usecases: dict[str, dict[str, str]] = field(default_factory=dict)

    def label_view(
        self,
        parent_id: str,
        model_id: str,
        text: str = "",
        left: float = 0,
        top: float = 0,
        width: float = 0,
        height: float = 13,
        visible: bool = True,
        bold: bool = False,
        word_wrap: bool = False,
        h_align: int | None = None,
    ) -> dict[str, Any]:
        self._seq += 1
        label_id = gen_id()
        elem: dict[str, Any] = {
            "_type": "LabelView",
            "_id": label_id,
            "_parent": ref(parent_id),
            "font": f"Arial;13;{1 if bold else 0}",
        }
        if not visible:
            elem["visible"] = False
        if left:
            elem["left"] = left
        if top:
            elem["top"] = top
        if width:
            elem["width"] = width
        if height:
            elem["height"] = height
        if text:
            elem["text"] = text
        if word_wrap:
            elem["wordWrap"] = True
        if h_align is not None:
            elem["horizontalAlignment"] = h_align
        return elem

    def name_compartment(
        self,
        parent_id: str,
        model_id: str,
        name: str,
        left: float,
        top: float,
        width: float,
        namespace: str = "Modèle DDL",
    ) -> tuple[str, dict[str, Any]]:
        comp_id = gen_id()
        stereo_id = gen_id()
        name_id = gen_id()
        ns_id = gen_id()
        prop_id = gen_id()

        stereo = self.label_view(comp_id, model_id, visible=False)
        stereo["_id"] = stereo_id

        name_label = self.label_view(
            comp_id, model_id, text=name, left=left + 5, top=top + 7,
            width=max(width - 10, 60), bold=True, word_wrap=True,
        )
        name_label["_id"] = name_id

        ns_label = self.label_view(
            comp_id, model_id, text=f"(from {namespace})", visible=False, width=229,
        )
        ns_label["_id"] = ns_id

        prop_label = self.label_view(comp_id, model_id, visible=False, h_align=1)
        prop_label["_id"] = prop_id

        compartment = {
            "_type": "UMLNameCompartmentView",
            "_id": comp_id,
            "_parent": ref(parent_id),
            "model": ref(model_id),
            "subViews": [stereo, name_label, ns_label, prop_label],
            "font": "Arial;13;0",
            "left": left,
            "top": top,
            "width": width,
            "height": 25,
            "stereotypeLabel": ref(stereo_id),
            "nameLabel": ref(name_id),
            "namespaceLabel": ref(ns_id),
            "propertyLabel": ref(prop_id),
        }
        return comp_id, compartment

    def hidden_compartment(self, parent_id: str, model_id: str, comp_type: str) -> dict[str, Any]:
        return {
            "_type": comp_type,
            "_id": gen_id(),
            "_parent": ref(parent_id),
            "model": ref(model_id),
            "visible": False,
            "font": "Arial;13;0",
            "left": 16,
            "top": 32,
            "width": 10,
            "height": 10,
        }

    def add_actor(self, name: str, left: int, top: int) -> None:
        model_id = gen_id()
        view_id = gen_id()
        self.actors[name] = {"model": model_id, "view": view_id, "left": left, "top": top}

        self.model_elements.append({
            "_type": "UMLActor",
            "_id": model_id,
            "_parent": ref(self.model_id),
            "name": name,
        })

        comp_id, compartment = self.name_compartment(
            view_id, model_id, name, left, top + 246, 80,
        )

        attr = self.hidden_compartment(view_id, model_id, "UMLAttributeCompartmentView")
        op = self.hidden_compartment(view_id, model_id, "UMLOperationCompartmentView")

        self.diagram_views.append({
            "_type": "UMLActorView",
            "_id": view_id,
            "_parent": ref(self.diagram_id),
            "model": ref(model_id),
            "subViews": [compartment, attr, op],
            "font": "Arial;13;0",
            "left": left,
            "top": top,
            "width": 30,
            "height": 60,
            "nameCompartment": ref(comp_id),
            "suppressAttributes": True,
            "suppressOperations": True,
            "attributeCompartment": ref(attr["_id"]),
            "operationCompartment": ref(op["_id"]),
        })

    def add_usecase(self, name: str, left: int, top: int, width: int = 150) -> None:
        model_id = gen_id()
        view_id = gen_id()
        height = 49 if len(name) > 22 else 41
        self.usecases[name] = {
            "model": model_id,
            "view": view_id,
            "left": left,
            "top": top,
            "width": width,
            "height": height,
        }

        self.model_elements.append({
            "_type": "UMLUseCase",
            "_id": model_id,
            "_parent": ref(self.model_id),
            "name": name,
        })

        comp_id, compartment = self.name_compartment(
            view_id, model_id, name, left, top, width,
        )
        attr = self.hidden_compartment(view_id, model_id, "UMLAttributeCompartmentView")
        op = self.hidden_compartment(view_id, model_id, "UMLOperationCompartmentView")
        rec = self.hidden_compartment(view_id, model_id, "UMLReceptionCompartmentView")
        tpl = self.hidden_compartment(view_id, model_id, "UMLTemplateParameterCompartmentView")
        ext = self.hidden_compartment(view_id, model_id, "UMLExtensionPointCompartmentView")

        self.diagram_views.append({
            "_type": "UMLUseCaseView",
            "_id": view_id,
            "_parent": ref(self.diagram_id),
            "model": ref(model_id),
            "subViews": [compartment, attr, op, rec, tpl, ext],
            "font": "Arial;13;0",
            "containerChangeable": True,
            "left": left,
            "top": top,
            "width": width,
            "height": height,
            "nameCompartment": ref(comp_id),
            "wordWrap": True,
            "suppressAttributes": True,
            "suppressOperations": True,
            "attributeCompartment": ref(attr["_id"]),
            "operationCompartment": ref(op["_id"]),
            "receptionCompartment": ref(rec["_id"]),
            "templateParameterCompartment": ref(tpl["_id"]),
            "extensionPointCompartment": ref(ext["_id"]),
        })

    def add_association(self, actor_name: str, usecase_name: str) -> None:
        actor = self.actors[actor_name]
        uc = self.usecases[usecase_name]
        assoc_id = gen_id()
        end1_id = gen_id()
        end2_id = gen_id()
        view_id = gen_id()

        self.model_elements.append({
            "_type": "UMLAssociation",
            "_id": assoc_id,
            "_parent": ref(actor["model"]),
            "end1": {
                "_type": "UMLAssociationEnd",
                "_id": end1_id,
                "_parent": ref(assoc_id),
                "reference": ref(actor["model"]),
            },
            "end2": {
                "_type": "UMLAssociationEnd",
                "_id": end2_id,
                "_parent": ref(assoc_id),
                "reference": ref(uc["model"]),
            },
        })

        ax = actor["left"] + 30
        ay = actor["top"] + 30
        ux = uc["left"] + uc["width"] // 2
        uy = uc["top"] + uc["height"] // 2

        name_lbl = gen_id()
        stereo_lbl = gen_id()
        prop_lbl = gen_id()

        self.diagram_views.append({
            "_type": "UMLAssociationView",
            "_id": view_id,
            "_parent": ref(self.diagram_id),
            "model": ref(assoc_id),
            "subViews": [
                {
                    "_type": "EdgeLabelView",
                    "_id": name_lbl,
                    "_parent": ref(view_id),
                    "model": ref(assoc_id),
                    "visible": False,
                    "font": "Arial;13;0",
                    "left": (ax + ux) // 2,
                    "top": (ay + uy) // 2,
                    "height": 13,
                    "alpha": 1.5707963267948966,
                    "distance": 15,
                    "hostEdge": ref(view_id),
                    "edgePosition": 1,
                },
                {
                    "_type": "EdgeLabelView",
                    "_id": stereo_lbl,
                    "_parent": ref(view_id),
                    "model": ref(assoc_id),
                    "font": "Arial;13;0",
                    "left": (ax + ux) // 2,
                    "top": (ay + uy) // 2 - 10,
                    "height": 13,
                    "alpha": 1.5707963267948966,
                    "distance": 30,
                    "hostEdge": ref(view_id),
                    "edgePosition": 1,
                },
                {
                    "_type": "EdgeLabelView",
                    "_id": prop_lbl,
                    "_parent": ref(view_id),
                    "model": ref(assoc_id),
                    "visible": False,
                    "font": "Arial;13;0",
                    "left": (ax + ux) // 2,
                    "top": (ay + uy) // 2 + 10,
                    "height": 13,
                    "alpha": -1.5707963267948966,
                    "distance": 15,
                    "hostEdge": ref(view_id),
                    "edgePosition": 1,
                },
            ],
            "font": "Arial;13;0",
            "head": ref(uc["view"]),
            "tail": ref(actor["view"]),
            "lineStyle": 1,
            "points": f"{ax}:{ay};{ux}:{uy}",
            "showVisibility": True,
            "nameLabel": ref(name_lbl),
            "stereotypeLabel": ref(stereo_lbl),
            "propertyLabel": ref(prop_lbl),
        })

    def add_include(self, source_name: str, target_name: str) -> None:
        source = self.usecases[source_name]
        target = self.usecases[target_name]
        include_id = gen_id()
        view_id = gen_id()

        source["model_elem"] = source.get("model_elem")
        # attach include to source use case model
        for elem in self.model_elements:
            if elem["_id"] == source["model"]:
                if "ownedElements" not in elem:
                    elem["ownedElements"] = []
                elem["ownedElements"].append({
                    "_type": "UMLInclude",
                    "_id": include_id,
                    "_parent": ref(source["model"]),
                    "source": ref(source["model"]),
                    "target": ref(target["model"]),
                })
                break

        sx = source["left"] + source["width"] // 2
        sy = source["top"] + source["height"]
        tx = target["left"] + target["width"] // 2
        ty = target["top"]

        name_lbl = gen_id()
        stereo_lbl = gen_id()
        prop_lbl = gen_id()

        self.diagram_views.append({
            "_type": "UMLIncludeView",
            "_id": view_id,
            "_parent": ref(self.diagram_id),
            "model": ref(include_id),
            "subViews": [
                {
                    "_type": "EdgeLabelView",
                    "_id": name_lbl,
                    "_parent": ref(view_id),
                    "model": ref(include_id),
                    "visible": False,
                    "font": "Arial;13;0",
                    "left": sx,
                    "top": (sy + ty) // 2,
                    "height": 13,
                    "alpha": 1.5707963267948966,
                    "distance": 15,
                    "hostEdge": ref(view_id),
                    "edgePosition": 1,
                },
                {
                    "_type": "EdgeLabelView",
                    "_id": stereo_lbl,
                    "_parent": ref(view_id),
                    "model": ref(include_id),
                    "font": "Arial;13;0",
                    "left": sx + 15,
                    "top": (sy + ty) // 2,
                    "width": 61,
                    "height": 13,
                    "alpha": -1.5385490843842793,
                    "distance": 31,
                    "hostEdge": ref(view_id),
                    "edgePosition": 1,
                    "text": "«include»",
                },
                {
                    "_type": "EdgeLabelView",
                    "_id": prop_lbl,
                    "_parent": ref(view_id),
                    "model": ref(include_id),
                    "visible": False,
                    "font": "Arial;13;0",
                    "left": sx,
                    "top": (sy + ty) // 2,
                    "height": 13,
                    "alpha": -1.5707963267948966,
                    "distance": 15,
                    "hostEdge": ref(view_id),
                    "edgePosition": 1,
                },
            ],
            "font": "Arial;13;0",
            "head": ref(target["view"]),
            "tail": ref(source["view"]),
            "lineStyle": 1,
            "points": f"{sx}:{sy};{tx}:{ty}",
            "showVisibility": True,
            "nameLabel": ref(name_lbl),
            "stereotypeLabel": ref(stereo_lbl),
            "propertyLabel": ref(prop_lbl),
        })

    def add_subject(self, name: str, left: int, top: int, width: int, height: int) -> None:
        view_id = gen_id()
        comp_id, compartment = self.name_compartment(
            view_id, self.subject_model_id, name, left, top, width,
        )
        self.diagram_views.insert(0, {
            "_type": "UMLUseCaseSubjectView",
            "_id": view_id,
            "_parent": ref(self.diagram_id),
            "model": ref(self.subject_model_id),
            "subViews": [compartment],
            "font": "Arial;13;0",
            "left": left,
            "top": top,
            "width": width,
            "height": height,
            "nameCompartment": ref(comp_id),
        })

        self.model_elements.insert(0, {
            "_type": "UMLUseCaseSubject",
            "_id": self.subject_model_id,
            "_parent": ref(self.model_id),
            "name": name,
        })

    def build(self) -> dict[str, Any]:
        return {
            "_type": "Project",
            "_id": self.project_id,
            "name": self.project_name,
            "ownedElements": [
                {
                    "_type": "UMLModel",
                    "_id": self.model_id,
                    "_parent": ref(self.project_id),
                    "name": "Modèle DDL",
                    "ownedElements": [
                        {
                            "_type": "UMLUseCaseDiagram",
                            "_id": self.diagram_id,
                            "_parent": ref(self.model_id),
                            "name": "Cas d'utilisation DDL",
                            "visible": True,
                            "defaultDiagram": True,
                            "ownedViews": self.diagram_views,
                        },
                        *self.model_elements,
                    ],
                }
            ],
        }


def main() -> None:
    b = Builder("Plateforme DDL — CENI RDC")

    # System boundary
    b.add_subject("Plateforme DDL CENI RDC", 260, 50, 940, 780)

    # Actors
    b.add_actor("Agent", 40, 120)
    b.add_actor("Secrétariat", 40, 340)
    b.add_actor("Développeur", 40, 560)
    b.add_actor("Direction Informatique", 1240, 160)
    b.add_actor("Administrateur", 1240, 480)

    # Authentication
    b.add_usecase("Se connecter", 580, 90, 130)
    b.add_usecase("Se déconnecter", 580, 140, 130)

    # Transversal
    b.add_usecase("Consulter tableau de bord", 420, 680, 170)
    b.add_usecase("Consulter détail demande", 620, 680, 170)
    b.add_usecase("Télécharger cahier des charges", 820, 680, 190)
    b.add_usecase("Consulter historique demande", 420, 740, 180)
    b.add_usecase("Consulter notifications", 640, 740, 160)

    # Agent
    b.add_usecase("Créer demande DDL", 320, 200, 150)
    b.add_usecase("Enregistrer brouillon", 320, 255, 150)
    b.add_usecase("Générer cahier des charges", 320, 310, 170)
    b.add_usecase("Soumettre demande", 320, 365, 140)
    b.add_usecase("Consulter mes demandes", 320, 420, 160)
    b.add_usecase("Resoumettre après correction", 320, 475, 180)

    # Secrétariat
    b.add_usecase("Accuser réception", 540, 240, 150)
    b.add_usecase("Transférer à la DI", 540, 295, 140)

    # DI
    b.add_usecase("Prendre en charge", 760, 190, 150)
    b.add_usecase("Mettre en attente", 760, 245, 140)
    b.add_usecase("Reprendre analyse", 760, 300, 150)
    b.add_usecase("Valider demande", 760, 355, 130)
    b.add_usecase("Rejeter demande", 760, 410, 130)
    b.add_usecase("Demander correction", 760, 465, 160)
    b.add_usecase("Définir délai prévisionnel", 760, 520, 180)
    b.add_usecase("Affecter développeurs", 760, 575, 160)
    b.add_usecase("Commenter (DI)", 760, 630, 130)

    # Développeur
    b.add_usecase("Consulter affectations", 980, 340, 170)
    b.add_usecase("Démarrer développement", 980, 395, 170)
    b.add_usecase("Passer en test", 980, 450, 130)
    b.add_usecase("Commenter (Dev)", 980, 505, 140)

    # Admin
    b.add_usecase("Gérer utilisateurs", 540, 560, 150)
    b.add_usecase("Consulter historique global", 540, 615, 190)
    b.add_usecase("Configurer notifications", 540, 670, 180)

    # Includes
    includes = [
        ("Générer cahier des charges", "Enregistrer brouillon"),
        ("Soumettre demande", "Générer cahier des charges"),
        ("Accuser réception", "Consulter détail demande"),
        ("Transférer à la DI", "Accuser réception"),
        ("Prendre en charge", "Consulter détail demande"),
        ("Valider demande", "Consulter détail demande"),
        ("Rejeter demande", "Consulter détail demande"),
        ("Demander correction", "Consulter détail demande"),
        ("Affecter développeurs", "Valider demande"),
        ("Démarrer développement", "Consulter affectations"),
        ("Passer en test", "Démarrer développement"),
        ("Resoumettre après correction", "Enregistrer brouillon"),
    ]
    for src, tgt in includes:
        b.add_include(src, tgt)

    # Associations — common
    all_roles = ["Agent", "Secrétariat", "Direction Informatique", "Développeur", "Administrateur"]
    common_uc = [
        "Se connecter", "Se déconnecter", "Consulter tableau de bord",
        "Consulter détail demande", "Télécharger cahier des charges",
        "Consulter historique demande", "Consulter notifications",
    ]
    for role in all_roles:
        for uc in common_uc:
            b.add_association(role, uc)

    agent_uc = [
        "Créer demande DDL", "Enregistrer brouillon", "Générer cahier des charges",
        "Soumettre demande", "Consulter mes demandes", "Resoumettre après correction",
    ]
    for uc in agent_uc:
        b.add_association("Agent", uc)

    secretariat_uc = ["Accuser réception", "Transférer à la DI"]
    for uc in secretariat_uc:
        b.add_association("Secrétariat", uc)

    di_uc = [
        "Prendre en charge", "Mettre en attente", "Reprendre analyse",
        "Valider demande", "Rejeter demande", "Demander correction",
        "Définir délai prévisionnel", "Affecter développeurs", "Commenter (DI)",
    ]
    for uc in di_uc:
        b.add_association("Direction Informatique", uc)

    dev_uc = [
        "Consulter affectations", "Démarrer développement",
        "Passer en test", "Commenter (Dev)",
    ]
    for uc in dev_uc:
        b.add_association("Développeur", uc)

    admin_uc = ["Gérer utilisateurs", "Consulter historique global", "Configurer notifications"]
    for uc in admin_uc:
        b.add_association("Administrateur", uc)

    output = os.path.join(
        os.path.dirname(__file__),
        "..",
        "phase-1",
        "diagramme-cas-utilisation-ddl.mdj",
    )
    output = os.path.normpath(output)
    os.makedirs(os.path.dirname(output), exist_ok=True)

    with open(output, "w", encoding="utf-8") as fh:
        json.dump(b.build(), fh, ensure_ascii=False, indent="\t")

    print(f"Generated: {output}")


if __name__ == "__main__":
    main()
