#!/usr/bin/env python3
"""Generate StarUML class diagram (.mdj) for the DDL platform domain model."""

from __future__ import annotations

import base64
import json
import os
from dataclasses import dataclass, field
from typing import Any


def gen_id() -> str:
    return base64.b64encode(os.urandom(12)).decode()


def ref(element_id: str) -> dict[str, str]:
    return {"$ref": element_id}


@dataclass
class ClassDiagramBuilder:
    project_name: str

    project_id: str = field(default_factory=gen_id)
    model_id: str = field(default_factory=gen_id)
    diagram_id: str = field(default_factory=gen_id)

    model_elements: list[dict[str, Any]] = field(default_factory=list)
    diagram_views: list[dict[str, Any]] = field(default_factory=list)
    classes: dict[str, dict[str, Any]] = field(default_factory=dict)
    enums: dict[str, dict[str, Any]] = field(default_factory=dict)

    def add_class(
        self,
        name: str,
        attributes: list[tuple[str, str, bool]],
        operations: list[str] | None = None,
        left: int = 0,
        top: int = 0,
        width: int = 220,
    ) -> None:
        operations = operations or []
        model_id = gen_id()
        view_id = gen_id()

        attr_models: list[dict[str, Any]] = []
        attr_views: list[dict[str, Any]] = []
        attr_top = top + 45

        for attr_name, attr_type, is_id in attributes:
            attr_id = gen_id()
            attr_view_id = gen_id()
            text = f"+{attr_name}: {attr_type}"
            if is_id:
                text += " {id}"

            attr_models.append({
                "_type": "UMLAttribute",
                "_id": attr_id,
                "_parent": ref(model_id),
                "name": attr_name,
                "visibility": "public",
                "isStatic": False,
                "isLeaf": False,
                "type": attr_type,
                "isReadOnly": False,
                "isOrdered": False,
                "isUnique": False,
                "isDerived": False,
                "aggregation": "none",
                "isID": is_id,
            })
            attr_views.append({
                "_type": "UMLAttributeView",
                "_id": attr_view_id,
                "_parent": ref("ATTR_COMP"),
                "model": ref(attr_id),
                "font": "Arial;13;0",
                "left": left + 5,
                "top": attr_top,
                "width": width - 10,
                "height": 13,
                "text": text,
                "horizontalAlignment": 0,
            })
            attr_top += 15

        op_models: list[dict[str, Any]] = []
        op_views: list[dict[str, Any]] = []
        op_top = attr_top + 5

        for operation in operations:
            op_id = gen_id()
            op_view_id = gen_id()
            op_models.append({
                "_type": "UMLOperation",
                "_id": op_id,
                "_parent": ref(model_id),
                "name": operation,
                "visibility": "public",
                "isStatic": False,
                "isLeaf": False,
                "isAbstract": False,
                "concurrency": "sequential",
                "isQuery": False,
                "isOrdered": False,
                "isUnique": False,
                "isDerived": False,
                "parameters": [],
            })
            op_views.append({
                "_type": "UMLOperationView",
                "_id": op_view_id,
                "_parent": ref("OP_COMP"),
                "model": ref(op_id),
                "font": "Arial;13;0",
                "left": left + 5,
                "top": op_top,
                "width": width - 10,
                "height": 13,
                "text": f"+{operation}",
                "horizontalAlignment": 0,
            })
            op_top += 15

        attr_comp_id = gen_id()
        op_comp_id = gen_id()
        name_comp_id = gen_id()

        for v in attr_views:
            v["_parent"] = ref(attr_comp_id)
        for v in op_views:
            v["_parent"] = ref(op_comp_id)

        stereo_id = gen_id()
        name_lbl_id = gen_id()
        ns_id = gen_id()
        prop_id = gen_id()

        name_compartment = {
            "_type": "UMLNameCompartmentView",
            "_id": name_comp_id,
            "_parent": ref(view_id),
            "model": ref(model_id),
            "subViews": [
                {
                    "_type": "LabelView", "_id": stereo_id, "_parent": ref(name_comp_id),
                    "visible": False, "font": "Arial;13;0",
                },
                {
                    "_type": "LabelView", "_id": name_lbl_id, "_parent": ref(name_comp_id),
                    "font": "Arial;13;1", "left": left + 5, "top": top + 7,
                    "width": width - 10, "height": 13, "text": name,
                },
                {
                    "_type": "LabelView", "_id": ns_id, "_parent": ref(name_comp_id),
                    "visible": False, "font": "Arial;13;0", "text": "(from Modèle DDL)",
                },
                {
                    "_type": "LabelView", "_id": prop_id, "_parent": ref(name_comp_id),
                    "visible": False, "font": "Arial;13;0",
                },
            ],
            "font": "Arial;13;0",
            "left": left,
            "top": top,
            "width": width,
            "height": 25,
            "stereotypeLabel": ref(stereo_id),
            "nameLabel": ref(name_lbl_id),
            "namespaceLabel": ref(ns_id),
            "propertyLabel": ref(prop_id),
        }

        attr_height = max(len(attributes) * 15, 10)
        op_height = max(len(operations) * 15, 10) if operations else 10
        total_height = 25 + attr_height + (op_height if operations else 0) + 10

        attr_compartment = {
            "_type": "UMLAttributeCompartmentView",
            "_id": attr_comp_id,
            "_parent": ref(view_id),
            "model": ref(model_id),
            "subViews": attr_views,
            "font": "Arial;13;0",
            "left": left,
            "top": top + 25,
            "width": width,
            "height": attr_height,
        }

        sub_views: list[dict[str, Any]] = [name_compartment, attr_compartment]

        if operations:
            op_compartment = {
                "_type": "UMLOperationCompartmentView",
                "_id": op_comp_id,
                "_parent": ref(view_id),
                "model": ref(model_id),
                "subViews": op_views,
                "font": "Arial;13;0",
                "left": left,
                "top": top + 25 + attr_height,
                "width": width,
                "height": op_height,
            }
            sub_views.append(op_compartment)
            view_extra = {
                "operationCompartment": ref(op_comp_id),
            }
        else:
            op_comp_id = gen_id()
            hidden_op = {
                "_type": "UMLOperationCompartmentView",
                "_id": op_comp_id,
                "_parent": ref(view_id),
                "model": ref(model_id),
                "visible": False,
                "font": "Arial;13;0",
                "left": left,
                "top": top + 25 + attr_height,
                "width": width,
                "height": 10,
            }
            sub_views.append(hidden_op)
            view_extra = {
                "operationCompartment": ref(op_comp_id),
                "suppressOperations": True,
            }

        class_view = {
            "_type": "UMLClassView",
            "_id": view_id,
            "_parent": ref(self.diagram_id),
            "model": ref(model_id),
            "subViews": sub_views,
            "font": "Arial;13;0",
            "left": left,
            "top": top,
            "width": width,
            "height": total_height,
            "nameCompartment": ref(name_comp_id),
            "attributeCompartment": ref(attr_comp_id),
            **view_extra,
        }

        class_model: dict[str, Any] = {
            "_type": "UMLClass",
            "_id": model_id,
            "_parent": ref(self.model_id),
            "name": name,
            "visibility": "public",
            "attributes": attr_models,
            "operations": op_models,
            "isAbstract": False,
            "isFinalSpecialization": False,
            "isLeaf": False,
            "isActive": False,
        }

        self.classes[name] = {
            "model": model_id,
            "view": view_id,
            "left": left,
            "top": top,
            "width": width,
            "height": total_height,
            "owned": [],
        }
        self.model_elements.append(class_model)
        self.diagram_views.append(class_view)

    def add_enum(self, name: str, literals: list[str], left: int, top: int, width: int = 180) -> None:
        model_id = gen_id()
        view_id = gen_id()
        literal_models: list[dict[str, Any]] = []
        literal_views: list[dict[str, Any]] = []
        lit_top = top + 58

        for literal in literals:
            lit_id = gen_id()
            lit_view_id = gen_id()
            literal_models.append({
                "_type": "UMLEnumerationLiteral",
                "_id": lit_id,
                "_parent": ref(model_id),
                "name": literal,
            })
            literal_views.append({
                "_type": "UMLEnumerationLiteralView",
                "_id": lit_view_id,
                "_parent": ref("LIT_COMP"),
                "model": ref(lit_id),
                "font": "Arial;13;0",
                "left": left + 5,
                "top": lit_top,
                "width": width - 10,
                "height": 13,
                "text": literal,
                "horizontalAlignment": 0,
            })
            lit_top += 15

        lit_comp_id = gen_id()
        name_comp_id = gen_id()
        stereo_id = gen_id()
        name_lbl_id = gen_id()
        ns_id = gen_id()
        prop_id = gen_id()

        for v in literal_views:
            v["_parent"] = ref(lit_comp_id)

        name_compartment = {
            "_type": "UMLNameCompartmentView",
            "_id": name_comp_id,
            "_parent": ref(view_id),
            "model": ref(model_id),
            "subViews": [
                {
                    "_type": "LabelView", "_id": stereo_id, "_parent": ref(name_comp_id),
                    "font": "Arial;13;0", "left": left + 5, "top": top + 7,
                    "width": width - 10, "height": 13, "text": "«enumeration»",
                },
                {
                    "_type": "LabelView", "_id": name_lbl_id, "_parent": ref(name_comp_id),
                    "font": "Arial;13;1", "left": left + 5, "top": top + 22,
                    "width": width - 10, "height": 13, "text": name,
                },
                {
                    "_type": "LabelView", "_id": ns_id, "_parent": ref(name_comp_id),
                    "visible": False, "font": "Arial;13;0", "text": "(from Modèle DDL)",
                },
                {
                    "_type": "LabelView", "_id": prop_id, "_parent": ref(name_comp_id),
                    "visible": False, "font": "Arial;13;0",
                },
            ],
            "font": "Arial;13;0",
            "left": left,
            "top": top,
            "width": width,
            "height": 38,
            "stereotypeLabel": ref(stereo_id),
            "nameLabel": ref(name_lbl_id),
            "namespaceLabel": ref(ns_id),
            "propertyLabel": ref(prop_id),
        }

        lit_height = max(len(literals) * 15, 10)
        total_height = 38 + lit_height + 5

        lit_compartment = {
            "_type": "UMLEnumerationLiteralCompartmentView",
            "_id": lit_comp_id,
            "_parent": ref(view_id),
            "model": ref(model_id),
            "subViews": literal_views,
            "font": "Arial;13;0",
            "left": left,
            "top": top + 38,
            "width": width,
            "height": lit_height,
        }

        enum_view = {
            "_type": "UMLEnumerationView",
            "_id": view_id,
            "_parent": ref(self.diagram_id),
            "model": ref(model_id),
            "subViews": [name_compartment, lit_compartment],
            "font": "Arial;13;0",
            "left": left,
            "top": top,
            "width": width,
            "height": total_height,
            "nameCompartment": ref(name_comp_id),
            "literalCompartment": ref(lit_comp_id),
        }

        enum_model = {
            "_type": "UMLEnumeration",
            "_id": model_id,
            "_parent": ref(self.model_id),
            "name": name,
            "literals": literal_models,
        }

        self.enums[name] = {
            "model": model_id,
            "view": view_id,
            "left": left,
            "top": top,
            "width": width,
            "height": total_height,
        }
        self.model_elements.append(enum_model)
        self.diagram_views.append(enum_view)

    def add_association(
        self,
        owner_class: str,
        class_a: str,
        role_a: str,
        mult_a: str,
        class_b: str,
        role_b: str,
        mult_b: str,
        name: str = "",
    ) -> None:
        a = self.classes[class_a]
        b = self.classes[class_b]
        assoc_id = gen_id()
        end1_id = gen_id()
        end2_id = gen_id()
        view_id = gen_id()

        association = {
            "_type": "UMLAssociation",
            "_id": assoc_id,
            "_parent": ref(self.classes[owner_class]["model"]),
            "name": name,
            "end1": {
                "_type": "UMLAssociationEnd",
                "_id": end1_id,
                "_parent": ref(assoc_id),
                "name": role_a,
                "reference": ref(a["model"]),
                "visibility": "public",
                "navigable": True,
                "aggregation": "none",
                "multiplicity": mult_a,
                "isReadOnly": False,
                "isOrdered": False,
                "isUnique": False,
                "isDerived": False,
                "isID": False,
            },
            "end2": {
                "_type": "UMLAssociationEnd",
                "_id": end2_id,
                "_parent": ref(assoc_id),
                "name": role_b,
                "reference": ref(b["model"]),
                "visibility": "public",
                "navigable": True,
                "aggregation": "none",
                "multiplicity": mult_b,
                "isReadOnly": False,
                "isOrdered": False,
                "isUnique": False,
                "isDerived": False,
                "isID": False,
            },
            "visibility": "public",
            "isDerived": False,
        }

        self.classes[owner_class]["owned"].append(association)

        ax = a["left"] + a["width"]
        ay = a["top"] + a["height"] // 2
        bx = b["left"]
        by = b["top"] + b["height"] // 2

        if abs(ax - bx) < abs(a["left"] - b["left"]):
            ax = a["left"]
            bx = b["left"] + b["width"]

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
                    "_type": "EdgeLabelView", "_id": name_lbl, "_parent": ref(view_id),
                    "model": ref(assoc_id), "visible": bool(name), "font": "Arial;13;0",
                    "left": (ax + bx) // 2, "top": (ay + by) // 2 - 10,
                    "height": 13, "text": name if name else "",
                    "hostEdge": ref(view_id), "edgePosition": 1,
                },
                {
                    "_type": "EdgeLabelView", "_id": stereo_lbl, "_parent": ref(view_id),
                    "model": ref(assoc_id), "font": "Arial;13;0",
                    "left": (ax + bx) // 2, "top": (ay + by) // 2,
                    "height": 13, "hostEdge": ref(view_id), "edgePosition": 1,
                },
                {
                    "_type": "EdgeLabelView", "_id": prop_lbl, "_parent": ref(view_id),
                    "model": ref(assoc_id), "visible": False, "font": "Arial;13;0",
                    "left": (ax + bx) // 2, "top": (ay + by) // 2 + 10,
                    "height": 13, "hostEdge": ref(view_id), "edgePosition": 1,
                },
            ],
            "font": "Arial;13;0",
            "head": ref(b["view"]),
            "tail": ref(a["view"]),
            "lineStyle": 1,
            "points": f"{ax}:{ay};{bx}:{by}",
            "showVisibility": True,
            "nameLabel": ref(name_lbl),
            "stereotypeLabel": ref(stereo_lbl),
            "propertyLabel": ref(prop_lbl),
        })

    def finalize_owned_associations(self) -> None:
        for class_name, data in self.classes.items():
            for assoc in data["owned"]:
                for elem in self.model_elements:
                    if elem.get("_id") == data["model"]:
                        if "ownedElements" not in elem:
                            elem["ownedElements"] = []
                        elem["ownedElements"].append(assoc)
                        break

    def build(self) -> dict[str, Any]:
        self.finalize_owned_associations()
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
                            "_type": "UMLClassDiagram",
                            "_id": self.diagram_id,
                            "_parent": ref(self.model_id),
                            "name": "Diagramme de classes DDL",
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
    b = ClassDiagramBuilder("Plateforme DDL — CENI RDC")

    # Enumerations
    b.add_enum("UserRole", [
        "AGENT", "SECRETARIAT", "DIRECTION_INFORMATIQUE", "DEVELOPPEUR", "ADMIN",
    ], left=40, top=30, width=200)

    b.add_enum("StatutDemande", [
        "BROUILLON", "SOUMISE", "RECUE_SECRETARIAT", "TRANSFEREE_DI",
        "EN_ANALYSE", "EN_ATTENTE", "VALIDEE", "REJETEE", "A_CORRIGER",
        "AFFECTEE", "EN_DEVELOPPEMENT", "EN_TEST", "TERMINEE", "CLOTUREE",
    ], left=280, top=30, width=210)

    b.add_enum("Priorite", ["BASSE", "MOYENNE", "HAUTE", "CRITIQUE"], left=530, top=30, width=150)

    # Core entities
    b.add_class("User", [
        ("id", "UUID", True),
        ("email", "String", False),
        ("password", "String", False),
        ("nom", "String", False),
        ("prenom", "String", False),
        ("telephone", "String", False),
        ("service", "String", False),
        ("role", "UserRole", False),
        ("actif", "Boolean", False),
    ], operations=[
        "fullName(): String",
        "findByIdentifiant(identifiant: String): User",
    ], left=60, top=280, width=230)

    b.add_class("Demande", [
        ("id", "UUID", True),
        ("numero", "String", False),
        ("statut", "StatutDemande", False),
        ("priorite", "Priorite", False),
        ("titre", "String", False),
        ("service_demandeur", "String", False),
        ("nom_demandeur", "String", False),
        ("email_demandeur", "String", False),
        ("telephone_demandeur", "String", False),
        ("date_souhaitee_livraison", "Date", False),
        ("contexte", "Text", False),
        ("problematique", "Text", False),
        ("objectif_general", "Text", False),
        ("objectifs_specifiques", "JSON", False),
        ("description_fonctionnelle", "Text", False),
        ("utilisateurs_cibles", "Text", False),
        ("hors_perimetre", "Text", False),
        ("contraintes_techniques", "Text", False),
        ("contraintes_reglementaires", "Text", False),
        ("dependances", "Text", False),
        ("motif_rejet", "Text", False),
        ("date_soumission", "DateTime", False),
        ("delai_previsionnel", "Date", False),
        ("auteur_id", "UUID", False),
    ], operations=[
        "aCahierDesCharges(): Boolean",
        "estAffecteeA(userId: UUID): Boolean",
    ], left=380, top=240, width=260)

    b.add_class("Commentaire", [
        ("id", "UUID", True),
        ("demande_id", "UUID", False),
        ("auteur_id", "UUID", False),
        ("contenu", "Text", False),
        ("interne", "Boolean", False),
    ], left=980, top=260, width=210)

    b.add_class("HistoriqueAction", [
        ("id", "UUID", True),
        ("demande_id", "UUID", False),
        ("utilisateur_id", "UUID", False),
        ("ancien_statut", "StatutDemande", False),
        ("nouveau_statut", "StatutDemande", False),
        ("action", "String", False),
        ("commentaire", "Text", False),
        ("metadonnees", "JSON", False),
        ("created_at", "DateTime", False),
    ], left=980, top=390, width=240)

    b.add_class("AffectationDev", [
        ("id", "UUID", True),
        ("demande_id", "UUID", False),
        ("developpeur_id", "UUID", False),
        ("affecte_par_id", "UUID", False),
        ("actif", "Boolean", False),
        ("created_at", "DateTime", False),
    ], left=980, top=540, width=220)

    b.add_class("Notification", [
        ("id", "UUID", True),
        ("user_id", "UUID", False),
        ("demande_id", "UUID", False),
        ("type", "String", False),
        ("titre", "String", False),
        ("message", "Text", False),
        ("lue", "Boolean", False),
        ("created_at", "DateTime", False),
    ], left=1240, top=320, width=220)

    b.add_class("Parametre", [
        ("cle", "String", True),
        ("valeur", "Text", False),
        ("description", "String", False),
        ("updated_at", "DateTime", False),
    ], left=420, top=820, width=200)

    b.add_class("NumerotationDdl", [
        ("annee", "Integer", True),
        ("dernier_numero", "Integer", False),
    ], left=680, top=820, width=200)

    # Associations (each role name must be globally unique — StarUML UML010)
    b.add_association("User", "User", "demandesCreees", "1", "Demande", "auteurDemande", "0..*")
    b.add_association("Demande", "Demande", "listeCommentaires", "1", "Commentaire", "demandeCommentee", "0..*")
    b.add_association("User", "User", "commentairesRediges", "1", "Commentaire", "auteurCommentaire", "0..*")
    b.add_association("Demande", "Demande", "historiqueActions", "1", "HistoriqueAction", "demandeTracee", "0..*")
    b.add_association("User", "User", "historiqueUtilisateur", "1", "HistoriqueAction", "utilisateurAction", "0..*")
    b.add_association("Demande", "Demande", "affectationsDev", "1", "AffectationDev", "demandeAffectee", "0..*")
    b.add_association("User", "User", "affectationsDeveloppeur", "1", "AffectationDev", "developpeurAffecte", "0..*")
    b.add_association("User", "User", "affectationsRealisees", "0..1", "AffectationDev", "affecteParDi", "0..*")
    b.add_association("Demande", "Demande", "notificationsLiees", "0..1", "Notification", "demandeNotifiee", "0..*")
    b.add_association("User", "User", "notificationsRecues", "1", "Notification", "destinataireNotif", "0..*")

    output = os.path.normpath(os.path.join(
        os.path.dirname(__file__), "..", "phase-1", "diagramme-classes-ddl.mdj",
    ))
    os.makedirs(os.path.dirname(output), exist_ok=True)

    with open(output, "w", encoding="utf-8") as fh:
        json.dump(b.build(), fh, ensure_ascii=False, indent="\t")

    print(f"Generated: {output}")


if __name__ == "__main__":
    main()
