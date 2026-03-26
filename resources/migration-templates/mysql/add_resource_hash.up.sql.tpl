alter table {{actor_permission_relations_table}}
    add column resource_hash char(64) not null default '' after resource;

update {{actor_permission_relations_table}}
    set resource_hash = sha2(resource, 256);

alter table {{actor_permission_relations_table}}
    alter column resource_hash drop default;

drop index {{actor_permission_relations_unique_rule_index}}
    on {{actor_permission_relations_table}};

create unique index {{actor_permission_relations_unique_rule_index}}
    on {{actor_permission_relations_table}} (context_id, actor_id, permission_id, negated, resource_hash);

alter table {{role_permission_relations_table}}
    add column resource_hash char(64) not null default '' after resource;

update {{role_permission_relations_table}}
    set resource_hash = sha2(resource, 256);

alter table {{role_permission_relations_table}}
    alter column resource_hash drop default;

drop index {{role_permission_relations_unique_rule_index}}
    on {{role_permission_relations_table}};

create unique index {{role_permission_relations_unique_rule_index}}
    on {{role_permission_relations_table}} (context_id, role_id, permission_id, negated, resource_hash);

