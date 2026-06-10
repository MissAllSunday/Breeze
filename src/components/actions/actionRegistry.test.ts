import { describe, expect, it } from "vitest";
import type { StatusAction } from "breezeTypesActions";
import { createRegistry } from "./actionRegistry";

const makeAction = (overrides: Partial<StatusAction> = {}): StatusAction => ({
	id: "test",
	label: "Test",
	icon: "🔧",
	order: 0,
	isVisible: () => true,
	...overrides,
});

describe("createRegistry", () => {
	it("register adds an action", () => {
		const registry = createRegistry<StatusAction>();
		registry.register(makeAction());
		expect(registry.get()).toHaveLength(1);
	});

	it("register accepts multiple actions at once", () => {
		const registry = createRegistry<StatusAction>();
		registry.register(
			makeAction({ id: "a" }),
			makeAction({ id: "b" }),
			makeAction({ id: "c" }),
		);
		expect(registry.get()).toHaveLength(3);
	});

	it("deduplicates by id — second registration is ignored", () => {
		const registry = createRegistry<StatusAction>();
		const action = makeAction({ id: "like", label: "Like" });
		const duplicate = makeAction({ id: "like", label: "Like again" });
		registry.register(action);
		registry.register(duplicate);
		expect(registry.get()).toHaveLength(1);
		expect(registry.get()[0].label).toBe("Like");
	});

	it("deduplicates within a single register call", () => {
		const registry = createRegistry<StatusAction>();
		const action = makeAction({ id: "like" });
		registry.register(action, action);
		expect(registry.get()).toHaveLength(1);
	});

	it("get returns actions sorted by order ascending", () => {
		const registry = createRegistry<StatusAction>();
		registry.register(
			makeAction({ id: "c", order: 30 }),
			makeAction({ id: "a", order: 10 }),
			makeAction({ id: "b", order: 20 }),
		);
		const ids = registry.get().map((a) => a.id);
		expect(ids).toEqual(["a", "b", "c"]);
	});

	it("get returns a copy — mutating the result does not affect the registry", () => {
		const registry = createRegistry<StatusAction>();
		registry.register(makeAction({ id: "original" }));
		const first = registry.get();
		first.push(makeAction({ id: "injected" }));
		expect(registry.get()).toHaveLength(1);
		expect(registry.get()[0].id).toBe("original");
	});

	it("two registry instances are fully independent", () => {
		const r1 = createRegistry<StatusAction>();
		const r2 = createRegistry<StatusAction>();
		r1.register(makeAction({ id: "like" }));
		expect(r2.get()).toHaveLength(0);
	});

	it("empty registry returns an empty array", () => {
		const registry = createRegistry<StatusAction>();
		expect(registry.get()).toEqual([]);
	});
});
