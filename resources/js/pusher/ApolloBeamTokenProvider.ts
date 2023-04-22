import {ITokenProvider, TokenProviderResponse} from "@pusher/push-notifications-web";
import apolloClient from "../apollo";
import beamsauthMutation from "../queries/beamsauth.mutation.gql";

export class ApolloBeamTokenProvider implements ITokenProvider {
    async fetchToken(userId: string): Promise<TokenProviderResponse> {
        const {data} = await apolloClient.mutate({
            mutation: beamsauthMutation,
            variables: {
                userId: userId
            }
        }).catch((error) => {
            console.error("Errror while fetching beams token: ", error);
            return Promise.resolve(undefined);
        });

        return Promise.resolve({
            token: data.beamsAuth.token
        });
    }

}