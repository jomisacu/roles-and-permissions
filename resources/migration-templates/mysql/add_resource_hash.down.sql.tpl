drop index {{role_permission_relations_unique_rule_index}}
    on {{role_permission_relations_table}};

create unique index {{role_permission_relations_unique_rule_index}}
    on {{role_permission_relations_table}} (context_id, role_id, permission_id, negated, resource(191));

alter table {{role_permission_relations_table}}
    drop column resource_hash;

drop index {{actor_permission_relations_unique_rule_index}}
    on {{actor_permission_relations_table}};

create unique index {{actor_permission_relations_unique_rule_index}}
    on {{actor_permission_relations_table}} (context_id, actor_id, permission_id, negated, resource(191));

alter table {{actor_permission_relations_table}}
    drop column resource_hash;

